<?php
/**
 * includes/functions.php
 * General-purpose reusable helper functions used across the app.
 */

/** Escape output safely for HTML (prevents XSS). */
function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

/** Format a date for display, e.g. 24 Sep 2026. */
function formatDate(?string $date): string
{
    if (!$date) return '-';
    $ts = strtotime($date);
    return $ts ? date('d M Y', $ts) : '-';
}

/** Format currency in Indian Rupees. */
function formatCurrency($amount): string
{
    return '₹' . number_format((float)$amount, 2);
}

/**
 * Calculate a student's attendance percentage.
 * Present Days / Total Recorded Days × 100
 */
function calculateAttendancePercentage(PDO $pdo, int $studentId): float
{
    $stmt = $pdo->prepare(
        "SELECT
            SUM(status = 'Present') AS present_days,
            COUNT(*) AS total_days
         FROM attendance
         WHERE student_id = ?"
    );
    $stmt->execute([$studentId]);
    $row = $stmt->fetch();

    if (!$row || (int)$row['total_days'] === 0) {
        return 0.0;
    }

    return round(
        ((int)$row['present_days'] / (int)$row['total_days']) * 100,
        1
    );
}

/**
 * Validate and move an uploaded image safely.
 * Returns the safe stored filename on success, or null on failure.
 * $prefix is prepended to the generated filename.
 */
function handleImageUpload(array $file, string $prefix = 'img'): ?string
{
    if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
        return null;
    }

    if ($file['size'] > MAX_UPLOAD_SIZE) {
        return null;
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mime, ALLOWED_IMAGE_TYPES, true)) {
        return null;
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if (!in_array($ext, ALLOWED_IMAGE_EXT, true)) {
        return null;
    }

    if (!is_dir(UPLOAD_DIR)) {
        mkdir(UPLOAD_DIR, 0755, true);
    }

    $safeName = $prefix . '_' . bin2hex(random_bytes(8)) . '.' . $ext;
    $destination = UPLOAD_DIR . $safeName;

    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        return null;
    }

    return $safeName;
}

/**
 * Resolve the students-table row that belongs to the CURRENTLY
 * LOGGED-IN user, using ONLY the session's user_id.
 *
 * This prevents a student from accessing another student's record
 * by changing a URL parameter.
 */
function getCurrentStudent(PDO $pdo): ?array
{
    if (!isset($_SESSION['user_id'])) {
        return null;
    }

    $stmt = $pdo->prepare(
        "SELECT
            s.*,
            c.class_name,
            p.name AS parent_name,
            p.contact_no AS parent_contact,
            p.email AS parent_email
         FROM students s
         LEFT JOIN classes c ON c.id = s.class_id
         LEFT JOIN parents p ON p.id = s.parent_id
         WHERE s.user_id = ?
         LIMIT 1"
    );

    $stmt->execute([$_SESSION['user_id']]);

    $row = $stmt->fetch();

    return $row ?: null;
}

/** Simple flash-message helper (session-based). */
function setFlash(string $type, string $message): void
{
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message
    ];
}

function getFlash(): ?array
{
    if (!empty($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }

    return null;
}


/* =========================================================
   CSRF PROTECTION
   ========================================================= */

/**
 * Generate or return the current CSRF token.
 *
 * The token is stored in the user's session and reused
 * until the session ends.
 */
function csrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

/**
 * Generate a hidden CSRF field for forms.
 *
 * Usage:
 * <?= csrfField() ?>
 */
function csrfField(): string
{
    return '<input type="hidden" name="csrf_token" value="' .
        e(csrfToken()) .
        '">';
}

/**
 * Verify the submitted CSRF token.
 *
 * Call this before processing any POST request that
 * changes database data.
 *
 * Returns true when the submitted token is valid.
 */
function verifyCsrf(): bool
{
    if (
        empty($_POST['csrf_token']) ||
        empty($_SESSION['csrf_token'])
    ) {
        return false;
    }

    return hash_equals(
        $_SESSION['csrf_token'],
        $_POST['csrf_token']
    );
}
function getCurrentTeacher(PDO $pdo): ?array
{
    if (!isset($_SESSION['user_id'])) {
        return null;
    }
    $stmt = $pdo->prepare("SELECT * FROM teachers WHERE user_id = ? LIMIT 1");
    $stmt->execute([$_SESSION['user_id']]);
    $row = $stmt->fetch();
    return $row ?: null;
}

/**
 * Distinct (class, division) pairs a teacher is assigned to, derived
 * from regular_timetable.teacher_id — the only existing table that
 * links a teacher to specific classes/divisions. Used to scope
 * attendance marking and student-information viewing to only the
 * classes a teacher actually teaches.
 */
function getTeacherAssignedClasses(PDO $pdo, int $teacherId): array
{
    $stmt = $pdo->prepare(
        "SELECT DISTINCT rt.class_id, rt.division, c.class_name
         FROM regular_timetable rt
         LEFT JOIN classes c ON c.id = rt.class_id
         WHERE rt.teacher_id = ?
         ORDER BY c.class_name, rt.division"
    );
    $stmt->execute([$teacherId]);
    return $stmt->fetchAll();
}