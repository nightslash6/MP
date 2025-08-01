<?php
session_start();
require 'config.php';

$conn = db_connect();

// Get user data
$user_data = null;
if (isset($_SESSION['user_id'])) {
    $stmt = $conn->prepare("SELECT user_id, name, email, user_role FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 1) {
        $user_data = $result->fetch_assoc();
    }
    $stmt->close();
}else{
    header('Location: login.php');
    exit;
}

// Share it globally for navbar
$GLOBALS['user_data'] = $user_data;

// Fetch Questions
$forensics_questions = $conn->query("SELECT * FROM my_forensics_questions ORDER BY difficulty, question_id")->fetch_all(MYSQLI_ASSOC);
$crypto_questions = $conn->query("SELECT * FROM my_crypto_questions ORDER BY difficulty, question_id")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Forensics Challenge</title>
    <link rel="stylesheet" href="mstyles.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        .modal.fade .modal-dialog {
            transform: translateY(-50px);
            transition: transform 0.3s ease-out, opacity 0.3s ease-out;
        }
        .modal.show .modal-dialog {
            transform: translateY(0);
        }
    </style>
</head>
<body>

<?php include 'navbar.php'; ?>

<div class="container mt-5">
    <div class="text-center mb-5">
        <h1 class="display-5 fw-bold">Forensics & Cryptography Lab</h1>
        <p class="text-muted">Click a challenge to open it in a popup and submit your solution.</p>
    </div>

    <!-- Forensics Section -->
    <h2 class="mb-4">🔍 Forensics Challenges</h2>
    <div class="row">
        <?php foreach ($forensics_questions as $index => $q): ?>
            <?php
                $key = "forensics_" . $q['question_id'];
                $isSolved = isset($submission_message[$key]) && str_contains($submission_message[$key], '✅');
            ?>
            <div class="col-md-6 mb-4">
                <div class="card shadow-sm h-100 <?= $isSolved ? 'border-success bg-light' : '' ?>">
                    <div class="card-body">
                        <h5 class="card-title"><?= htmlspecialchars($q['question_text']) ?></h5>
                        <p class="text-muted">Difficulty: <?= htmlspecialchars($q['difficulty']) ?></p>
                        <button class="btn <?= $isSolved ? 'btn-success' : 'btn-outline-primary' ?> w-100"
                                data-bs-toggle="modal"
                                data-bs-target="#modal-forensics-<?= $index ?>">
                            <?= $isSolved ? '✅ Completed' : 'View Challenge' ?>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Modal -->
            <div class="modal fade" id="modal-forensics-<?= $index ?>" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                    <div class="modal-content">
                        <form method="POST">
                            <div class="modal-header">
                                <h5 class="modal-title"><?= htmlspecialchars($q['question_text']) ?></h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <p><?= nl2br(htmlspecialchars($q['description'])) ?></p>
                                <input type="hidden" name="category" value="forensics">
                                <input type="hidden" name="question_id" value="<?= $q['question_id'] ?>">

                                <?php if ($isSolved): ?>
                                    <div class="alert alert-success">✅ You already solved this challenge correctly.</div>
                                <?php elseif ($q['question_type'] === 'MCQ' && !empty($q['options'])):
                                    $options = json_decode($q['options'], true);
                                    if (is_array($options)):
                                        foreach ($options as $opt): ?>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="user_flag" value="<?= htmlspecialchars($opt) ?>" required>
                                                <label class="form-check-label"><?= htmlspecialchars($opt) ?></label>
                                            </div>
                                        <?php endforeach; endif;
                                else: ?>
                                    <div class="mb-3">
                                        <label class="form-label">Enter Flag</label>
                                        <input type="text" name="user_flag" class="form-control" required>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="modal-footer">
                                <?php if (!$isSolved): ?>
                                    <button type="submit" name="submit_flag" class="btn btn-primary">Submit</button>
                                <?php endif; ?>
                                <?= $submission_message[$key] ?? '' ?>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <hr class="my-5">

    <!-- Crypto Section -->
    <h2 class="mb-4">🔐 Cryptography Challenges</h2>
    <div class="row">
        <?php foreach ($crypto_questions as $index => $q): ?>
            <?php
                $key = "crypto_" . $q['question_id'];
                $isSolved = isset($submission_message[$key]) && str_contains($submission_message[$key], '✅');
            ?>
            <div class="col-md-6 mb-4">
                <div class="card shadow-sm h-100 <?= $isSolved ? 'border-success bg-light' : '' ?>">
                    <div class="card-body">
                        <h5 class="card-title"><?= htmlspecialchars($q['question_text']) ?></h5>
                        <p class="text-muted">Difficulty: <?= htmlspecialchars($q['difficulty']) ?></p>
                        <button class="btn <?= $isSolved ? 'btn-success' : 'btn-outline-primary' ?> w-100"
                                data-bs-toggle="modal"
                                data-bs-target="#modal-crypto-<?= $index ?>">
                            <?= $isSolved ? '✅ Completed' : 'View Challenge' ?>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Modal -->
            <div class="modal fade" id="modal-crypto-<?= $index ?>" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                    <div class="modal-content">
                        <form method="POST">
                            <div class="modal-header">
                                <h5 class="modal-title"><?= htmlspecialchars($q['question_text']) ?></h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <p><?= nl2br(htmlspecialchars($q['description'])) ?></p>
                                <input type="hidden" name="category" value="crypto">
                                <input type="hidden" name="question_id" value="<?= $q['question_id'] ?>">

                                <?php if ($isSolved): ?>
                                    <div class="alert alert-success">✅ You already solved this challenge correctly.</div>
                                <?php elseif ($q['question_type'] === 'MCQ' && !empty($q['options'])):
                                    $options = json_decode($q['options'], true);
                                    if (is_array($options)):
                                        foreach ($options as $opt): ?>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="user_flag" value="<?= htmlspecialchars($opt) ?>" required>
                                                <label class="form-check-label"><?= htmlspecialchars($opt) ?></label>
                                            </div>
                                        <?php endforeach; endif;
                                else: ?>
                                    <div class="mb-3">
                                        <label class="form-label">Enter Flag</label>
                                        <input type="text" name="user_flag" class="form-control" required>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="modal-footer">
                                <?php if (!$isSolved): ?>
                                    <button type="submit" name="submit_flag" class="btn btn-primary">Submit</button>
                                <?php endif; ?>
                                <?= $submission_message[$key] ?? '' ?>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

</body>
</html>
