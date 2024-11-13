<?php
require_once '../login/dbh.inc.php';
session_start();

if (isset($_GET['id'])) {
    $announcement_id = $_GET['id'];

    $user = $_SESSION['user'];
    $admin_id = $_SESSION['user']['admin_id'];
    $first_name = $_SESSION['user']['first_name'];
    $last_name = $_SESSION['user']['last_name'];
    $email = $_SESSION['user']['email'];
    $contact_number = $_SESSION['user']['contact_number'];
    $department_id = $_SESSION['user']['department_id'];

    try {
        $query = "
            SELECT a.*, ad.first_name, ad.last_name,
                STRING_AGG(DISTINCT yl.year_level, ', ') AS year_levels,
                STRING_AGG(DISTINCT d.department_name, ', ') AS departments,
                STRING_AGG(DISTINCT c.course_name, ', ') AS courses
            FROM announcement a
            JOIN admin ad ON a.admin_id = ad.admin_id
            LEFT JOIN announcement_year_level ayl ON a.announcement_id = ayl.announcement_id
            LEFT JOIN year_level yl ON ayl.year_level_id = yl.year_level_id
            LEFT JOIN announcement_department adp ON a.announcement_id = adp.announcement_id
            LEFT JOIN department d ON adp.department_id = d.department_id
            LEFT JOIN announcement_course ac ON a.announcement_id = ac.announcement_id
            LEFT JOIN course c ON ac.course_id = c.course_id
            WHERE a.announcement_id = :announcement_id
            GROUP BY a.announcement_id, ad.first_name, ad.last_name";

        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':announcement_id', $announcement_id, PDO::PARAM_INT);
        $stmt->execute();

        $announcement = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($announcement) {
            $title = $announcement['title'];
            $description = $announcement['description'];
            $image = $announcement['image'];
            $admin_name = $announcement['first_name'] . ' ' . $announcement['last_name'];
            $updated_at = date('F d, Y', strtotime($announcement['updated_at']));
            $year_levels = explode(',', $announcement['year_levels']);
            $departments = explode(',', $announcement['departments']);
            $courses = explode(',', $announcement['courses']);
        } else {
            echo "<p class='text-center'>Announcement not found.</p>";
            exit;
        }
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
        exit;
    }
} else {
    echo "<p class='text-center'>Invalid announcement ID.</p>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title); ?></title>
    <?php include '../cdn/head.html'; ?>
    <link rel="stylesheet" href="css/admin.css">
</head>

<body>

    <?php include '../cdn/navbar.php'; ?>

    <div class="container-fluid mt-5">
        <div class="row g-4">
            <div class="col-md-2 sidebars sidebar-left d-none d-md-block">
                <div class="sticky-sidebar pt-5 m-0">
                    <div class="sidebar">
                        <div class="left-card">
                            <div class="d-flex flex-column">
                                <a href="admin.php" class="btn nav-btn mb-3"><i class="bi bi-house"></i> Home</a>
                                <a class="btn nav-btn mb-3" href="manage.php"><i class="bi bi-kanban"></i> Manage Post</a>
                                <a class="btn nav-btn mb-3" href="create.php"><i class="bi bi-megaphone"></i> Create Announcement</a>
                                <a class="btn nav-btn mb-3" href="#"><i class="bi bi-clipboard"></i> Logs</a>
                                <a class="btn nav-btn" href="manage_student.php"><i class="bi bi-person-plus"></i> Manage Student Account</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-8 main-content pt-5 px-5">
                <div class="feed-container">
                    <div class="card mb-3">
                        <div class="profile-container d-flex px-3 pt-3">
                            <div class="profile-pic">
                                <img class="img-fluid" src="img/test pic.jpg" alt="Admin Profile Picture">
                            </div>
                            <p class="ms-1 mt-1"><?php echo htmlspecialchars($admin_name); ?></p>
                        </div>

                        <div class="image-container mx-3">
                            <a href="uploads/<?php echo htmlspecialchars($image); ?>" data-lightbox="image-<?php echo $announcement_id; ?>" data-title="<?php echo htmlspecialchars($title); ?>">
                                <img src="uploads/<?php echo htmlspecialchars($image); ?>" alt="Post Image" class="img-fluid">
                            </a>
                        </div>

                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($title); ?></h5>
                            <div class="card-text">
                                <p class="mb-2"><?php echo htmlspecialchars($description); ?></p>

                                Tags:
                                <?php
                                $all_tags = array_merge($year_levels, $departments, $courses);
                                foreach ($all_tags as $tag) : ?>
                                    <span class="badge rounded-pill bg-danger mb-2"><?php echo htmlspecialchars(trim($tag)); ?></span>
                                <?php endforeach; ?>
                            </div>

                            <small>Updated at <?php echo htmlspecialchars($updated_at); ?></small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>



    <?php include '../cdn/body.html'; ?>
</body>

</html>