<?php
session_start();
$host = '193.154.202.54'; //IP
$dbname = 'kremsguesserdb';
$user = 'kremsguesser';
$pass = '123mysql';

$message = "";
$selectedProject = null;
$issues = [];

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_project'])) {
        $title = $_POST['title'];
        $description = $_POST['description'];
        $stmt = $pdo->prepare("INSERT INTO projects (title, description, created_at, user_id) VALUES (:title, :description, NOW(), 1)");
        $stmt->execute([':title' => $title, ':description' => $description]);
        $message = $stmt->rowCount() > 0 ? "Projekt erfolgreich hinzugefügt!" : "Fehler beim Hinzufügen des Projekts.";
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_project'])) {
        $project_id = $_POST['project_id'];
        $title = $_POST['title'];
        $description = $_POST['description'];
        $stmt = $pdo->prepare("UPDATE projects SET title = :title, description = :description WHERE project_id = :project_id");
        $stmt->execute([':title' => $title, ':description' => $description, ':project_id' => $project_id]);
        $message = $stmt->rowCount() > 0 ? "Projekt erfolgreich bearbeitet!" : "Keine Änderungen vorgenommen.";
    }

    if (isset($_GET['delete_project_id']) && filter_var($_GET['delete_project_id'], FILTER_VALIDATE_INT)) {
        $delete_id = (int)$_GET['delete_project_id'];
        $stmt = $pdo->prepare("DELETE FROM projects WHERE project_id = :delete_id");
        $stmt->execute([':delete_id' => $delete_id]);
        $message = $stmt->rowCount() > 0 ? "Projekt erfolgreich gelöscht!" : "Fehler beim Löschen des Projekts.";
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_issue'])) {
        $project_id = $_POST['project_id'];
        $category = $_POST['category'];
        $description = $_POST['description'];
        $stmt = $pdo->prepare("INSERT INTO issues (project_id, category, description, created_at) VALUES (:project_id, :category, :description, NOW())");
        $stmt->execute([':project_id' => $project_id, ':category' => $category, ':description' => $description]);
        $message = $stmt->rowCount() > 0 ? "Issue erfolgreich hinzugefügt!" : "Fehler beim Hinzufügen des Issues.";
    }

    if (isset($_GET['delete_issue_id']) && filter_var($_GET['delete_issue_id'], FILTER_VALIDATE_INT)) {
        $delete_id = (int)$_GET['delete_issue_id'];
        $stmt = $pdo->prepare("DELETE FROM issues WHERE issue_id = :delete_id");
        $stmt->execute([':delete_id' => $delete_id]);
        $message = $stmt->rowCount() > 0 ? "Issue erfolgreich gelöscht!" : "Fehler beim Löschen des Issues.";
    }

    $stmt = $pdo->query("SELECT * FROM projects");
    $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (isset($_GET['project_id']) && filter_var($_GET['project_id'], FILTER_VALIDATE_INT)) {
        $project_id = (int)$_GET['project_id'];
        $stmt = $pdo->prepare("SELECT * FROM projects WHERE project_id = :project_id");
        $stmt->execute([':project_id' => $project_id]);
        $selectedProject = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($selectedProject && isset($_GET['show_issues'])) {
            $stmt = $pdo->prepare("SELECT * FROM issues WHERE project_id = :project_id");
            $stmt->execute([':project_id' => $project_id]);
            $issues = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    }
} catch (PDOException $e) {
    $message = "Datenbankfehler: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Projektmanager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #eef2f7;
            color: #212529;
            font-family: Arial, sans-serif;
        }
        .card {
            margin-bottom: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
        }
        .card-header {
            background-color: #007bff;
            color: white;
            font-weight: bold;
        }
        .btn-primary {
            background-color: #28a745;
            border-color: #28a745;
        }
        .btn-primary:hover {
            background-color: #218838;
            border-color: #1e7e34;
        }
        .btn-outline-secondary:hover {
            background-color: #f8d7da;
            color: #212529;
        }
        table thead {
            background-color: #ffc107;
            color: #343a40;
        }
        .alert {
            margin-top: 15px;
            background-color: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }
        input, select, textarea {
            border: 1px solid #ced4da;
            border-radius: 4px;
        }
    </style>
</head>
<body>
<div class="container mt-5">
    <h1 class="text-center mb-4" style="color: #343a40;">Projektmanager</h1>

    <?php if (!empty($message)): ?>
        <div class="alert text-center"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header">Projekte</div>
        <div class="card-body">
            <table class="table table-bordered">
                <thead>
                <tr>
                    <th>#</th>
                    <th>Titel</th>
                    <th>Beschreibung</th>
                    <th>Erstellt am</th>
                    <th>Aktionen</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($projects as $project): ?>
                    <tr>
                        <td><?= htmlspecialchars($project['project_id']) ?></td>
                        <td><?= htmlspecialchars($project['title']) ?></td>
                        <td><?= htmlspecialchars($project['description']) ?></td>
                        <td><?= htmlspecialchars($project['created_at']) ?></td>
                        <td>
                            <a href="?project_id=<?= $project['project_id'] ?>" class="btn btn-outline-secondary btn-sm">Details</a>
                            <a href="?project_id=<?= $project['project_id'] ?>&show_issues=1" class="btn btn-outline-primary btn-sm">Issues</a>
                            <a href="?delete_project_id=<?= $project['project_id'] ?>" class="btn btn-danger btn-sm">Löschen</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="card">
        <div class="card-header">Neues Projekt hinzufügen</div>
        <div class="card-body">
            <form method="POST">
                <div class="mb-3">
                    <label for="title" class="form-label">Titel</label>
                    <input type="text" id="title" name="title" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="description" class="form-label">Beschreibung</label>
                    <textarea id="description" name="description" class="form-control" rows="3" required></textarea>
                </div>
                <button type="submit" name="add_project" class="btn btn-primary">Hinzufügen</button>
            </form>
        </div>
    </div>

    <?php if ($selectedProject): ?>
        <div class="card">
            <div class="card-header">Projekt: <?= htmlspecialchars($selectedProject['title']) ?></div>
            <div class="card-body">
                <p><strong>Beschreibung:</strong> <?= htmlspecialchars($selectedProject['description']) ?></p>
                <p><strong>Erstellt am:</strong> <?= htmlspecialchars($selectedProject['created_at']) ?></p>
            </div>
        </div>

        <?php if (isset($_GET['show_issues'])): ?>
            <div class="card">
                <div class="card-header">Issues für <?= htmlspecialchars($selectedProject['title']) ?></div>
                <div class="card-body">
                    <?php if ($issues): ?>
                        <ul class="list-group">
                            <?php foreach ($issues as $issue): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span>
                                        <strong><?= htmlspecialchars($issue['category']) ?>:</strong>
                                        <?= htmlspecialchars($issue['description']) ?>
                                    </span>
                                    <a href="?delete_issue_id=<?= $issue['issue_id'] ?>" class="btn btn-danger btn-sm">Löschen</a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p>Keine Issues vorhanden.</p>
                    <?php endif; ?>

                    <form method="POST" class="mt-4">
                        <input type="hidden" name="project_id" value="<?= $selectedProject['project_id'] ?>">
                        <div class="mb-3">
                            <label for="category" class="form-label">Kategorie</label>
                            <select id="category" name="category" class="form-select" required>
                                <option value="Ticket">Ticket</option>
                                <option value="Feature">Feature</option>
                                <option value="Task">Task</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Beschreibung</label>
                            <textarea id="description" name="description" class="form-control" required></textarea>
                        </div>
                        <button type="submit" name="add_issue" class="btn btn-primary">Issue hinzufügen</button>
                    </form>
                </div>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
