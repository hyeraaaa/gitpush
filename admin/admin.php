<?php
require_once '../login/dbh.inc.php'; // DATABASE CONNECTION
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../login/login.php");
    exit();
}

//Get info from admin session
$user = $_SESSION['user'];
$admin_id = $_SESSION['user']['admin_id'];
$first_name = $_SESSION['user']['first_name'];
$last_name = $_SESSION['user']['last_name'];
$email = $_SESSION['user']['email'];
$contact_number = $_SESSION['user']['contact_number'];
$department_id = $_SESSION['user']['department_id'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>ISMS Portal</title>
    <meta charset="utf-8" />
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1, shrink-to-fit=no" />

    <!-- head CDN links -->
    <?php include '../cdn/head.html'; ?>
    <link rel="stylesheet" href="css/admin.css">
    <link rel="stylesheet" href="css/modals.css">
    <link rel="stylesheet" href="css/sidebar.css">
    <link rel="stylesheet" href="css/feeds-card.css">
</head>

<body>
    <header>
        <?php include '../cdn/navbar.php' ?>

        <nav class="navbar nav-bottom fixed-bottom d-block d-lg-none mt-5">
            <div class="container-fluid justify-content-around">
                <a href="admin.php" class="btn nav-bottom-btn active">
                    <i class="bi bi-house"></i>
                    <span class="icon-label">Home</span>
                </a>

                <a class="btn nav-bottom-btn" href="manage.php">
                    <i class="bi bi-kanban"></i>
                    <span class="icon-label">Manage</span>
                </a>

                <a class="btn nav-bottom-btn" href="create.php">
                    <i class="bi bi-megaphone"></i>
                    <span class="icon-label">Create</span>
                </a>

                <a class="btn nav-bottom-btn" href="#">
                    <i class="bi bi-clipboard"></i>
                    <span class="icon-label">Logs</span>
                </a>

                <a class="btn nav-bottom-btn" href="manage_student.php">
                    <i class="bi bi-person-plus"></i>
                    <span class="icon-label">Students</span>
                </a>

            </div>
        </nav>
        <div class="offcanvas offcanvas-start" tabindex="-1" id="offcanvasNavbar" aria-labelledby="offcanvasNavbarLabel">
            <div class="offcanvas-header">
                <h5 class="offcanvas-title" id="offcanvasNavbarLabel">Menu</h5>
                <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
            </div>
            <div class="offcanvas-body">
                <!-- Sidebar Content -->
                <div class="sidebar">
                    <div class="card">
                        <div class="card-body d-flex flex-column">
                            <a href="admin.php" class="btn active mb-3"><i class="bi bi-house"></i> Home</a>
                            <a class="btn mb-3" href="create.php"><i class="bi bi-megaphone"></i> Create Announcement</a>
                            <a class="btn mb-3" href="#"><i class="bi bi-kanban"></i> Manage Post</a>
                            <a class="btn" href="#"><i class="bi bi-clipboard"></i> Logs</a>
                            <a class="btn" href="manage_student.php"><i class="bi bi-person-plus"></i> Manage Student Account</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>
    <main>
        <div class="container-fluid pt-5">
            <div class="row">
                <!-- left sidebar -->
                <div class="col-lg-3 sidebar sidebar-left d-none d-lg-block">
                    <div class="sticky-sidebar pt-3 m-0 p-2">
                        <ul class="nav flex-column">
                            <li class="nav-item">
                                <a href=""><i class="bi bi-graph-up-arrow"></i>Dashboard</a>
                            </li>

                            <li class="nav-item">
                                <a href=""><i class="bi bi-house"></i>Feed</a>
                            </li>

                            <li class="nav-item">
                                <a href="manage.php"><i class="bi bi-person-circle"></i>My Profile</a>
                            </li>

                            <li class="nav-item">
                                <a href="create.php"><i class="bi bi-plus-circle"></i>Create Announcement</a>
                            </li>

                            <li class="nav-item">
                                <a href=""><i class="bi bi-journal"></i>Logs</a>
                            </li>

                            <li class="nav-item">
                                <a href=""><i class="bi bi-person-badge"></i>Manage Accounts</a>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- main content -->
                <div class="col-12 col-xxl-9 col-lg-8 main-content pt-4 px-5">
                    <div class="row g-0">
                        <div class="col-xxl-7 col-lg-12 feed-container">
                            <?php
                            require_once '../login/dbh.inc.php';

                            try {
                                // Query to get the announcements along with the year level, department, and course
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
                            GROUP BY a.announcement_id, ad.first_name, ad.last_name
                            ORDER BY a.updated_at DESC";

                                $stmt = $pdo->prepare($query);
                                $stmt->execute();

                                $announcements = $stmt->fetchAll(PDO::FETCH_ASSOC);

                                if ($announcements) {
                                    foreach ($announcements as $row) {
                                        $announcement_id = $row['announcement_id'];
                                        $title = $row['title'];
                                        $description = $row['description'];
                                        $image = $row['image'];
                                        $announcement_admin_id = $row['admin_id'];
                                        $admin_first_name = $row['first_name'];
                                        $admin_last_name = $row['last_name'];
                                        $admin_name =  $admin_first_name . ' ' . $admin_last_name;
                                        $updated_at = date('F d, Y', strtotime($row['updated_at']));
                                        $year_levels = !empty($row['year_levels']) ? explode(',', $row['year_levels']) : [''];
                                        $departments = !empty($row['departments']) ? explode(',', $row['departments']) : [''];
                                        $courses = !empty($row['courses']) ? explode(',', $row['courses']) : [''];
                            ?>

                                        <div class="card mb-3">
                                            <div class="profile-container d-flex px-3 pt-3">
                                                <div class="profile-pic">
                                                    <img class="img-fluid" src="img/test pic.jpg" alt="">
                                                </div>
                                                <p class="ms-1 mt-1"><?php echo htmlspecialchars($admin_name); ?></p>
                                                <?php if ($admin_id === $announcement_admin_id) : ?>
                                                    <div class="dropdown ms-auto">
                                                        <span id="dropdownMenuButton<?php echo $announcement_id; ?>" data-bs-toggle="dropdown" aria-expanded="false">
                                                            <i class="bi bi-three-dots"></i>
                                                        </span>
                                                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuButton<?php echo $announcement_id; ?>">
                                                            <li><a class="dropdown-item" href="edit_announcement.php?id=<?php echo $announcement_id; ?>">Edit</a></li>
                                                            <li>
                                                                <a class="dropdown-item text-danger" href="#"
                                                                    data-bs-toggle="modal"
                                                                    data-bs-target="#deletePost"
                                                                    data-announcement-id="<?php echo $announcement_id; ?>">Delete</a>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                <?php endif; ?>
                                            </div>

                                            <div class="image-container mx-3" style="position: relative; overflow: hidden;">
                                                <div class="blur-background"></div>
                                                <a href="uploads/<?php echo htmlspecialchars($image); ?>" data-lightbox="image-<?php echo $announcement_id; ?>" data-title="<?php echo htmlspecialchars($title); ?>">
                                                    <img src="uploads/<?php echo htmlspecialchars($image); ?>" alt="Post Image" class="img-fluid">
                                                </a>
                                            </div>

                                            <script src="blurr.js"></script>

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

                            <?php
                                    }
                                } else {
                                    echo '<p class="text-center">No announcements found.</p>';
                                }
                            } catch (PDOException $e) {
                                // Handle any errors that occur during query execution
                                echo "Error: " . $e->getMessage();
                            }
                            ?>

                        </div>

                        <div class="col-lg-5 announcement-card d-none d-xxl-block">
                            <?php
                            require_once '../login/dbh.inc.php';

                            try {
                                $query = "SELECT a.*, b.first_name, b.last_name 
                                FROM announcement a 
                                JOIN admin b ON a.admin_id = b.admin_id 
                                ORDER BY a.updated_at DESC 
                                LIMIT 3";


                                $stmt = $pdo->prepare($query);
                                $stmt->execute();

                                $announcements = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            ?>
                                <div class="sticky-recent-post">
                                    <div class="filter">
                                        <div class="card latest-card p-2">
                                            <div class="card-body">
                                                <p class="card-title mb-3">RECENT POSTS</p>
                                                <div class="posts">
                                                    <?php
                                                    if ($announcements) {
                                                        foreach ($announcements as $row) {
                                                            $id = $row['announcement_id'];
                                                            $title = $row['title'];
                                                            $image = $row['image'];
                                                            $admin_first_name = $row['first_name'];
                                                            $admin_last_name = $row['last_name'];
                                                            $admin_name =  $admin_first_name . ' ' . $admin_last_name;
                                                    ?>
                                                            <div class="d-flex flex-column recent mb-2">
                                                                <div class="row">
                                                                    <div class="col-md-8 recent-profile-container">
                                                                        <div class="recent-container d-flex">
                                                                            <img class="profile-picture" src="img/profile.jpg" alt="">
                                                                            <p class="mt-1 ms-2"><?php echo htmlspecialchars($admin_name) ?></p>
                                                                        </div>

                                                                        <div class="title-container mt-0">
                                                                            <a style="color:black; text-decoration: none;" href="try.php?id=<?php echo $id; ?>"><?php echo htmlspecialchars($title); ?></a>
                                                                        </div>

                                                                    </div>

                                                                    <div class="col-md-4 post-img">
                                                                        <div class="post-img-container">
                                                                            <img class="post-image" src="uploads/<?php echo htmlspecialchars($image); ?>" alt="Post Image" class="img-fluid">
                                                                        </div>
                                                                    </div>
                                                                </div>


                                                            </div>
                                                            <hr>
                                                    <?php
                                                        }
                                                    } else {
                                                        echo '<p class="text-center" >No announcements found.</p>';
                                                    }
                                                    ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php
                            } catch (PDOException $e) {
                                // Handle any errors that occur during query execution
                                echo "Error: " . htmlspecialchars($e->getMessage());
                            }
                            ?>

                        </div>
                    </div>
                </div>







                <!-- Delete Post Modal -->
                <div class="modal fade" id="deletePost" tabindex="-1" aria-labelledby="deletePost" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered modal-sm">
                        <div class="modal-content custom" style="border-radius: 15px;">
                            <div class="modal-header pb-1" style="border: none">
                                <h1 class="modal-title fs-5" id="exampleModalLabel">Delete Post?</h1>
                                <button type="button" class="btn-close delete-modal-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body py-0" style="border: none;">
                                <p style="font-size: 15px;">Once you delete this post, it can't be restored.</p>
                            </div>
                            <div class="modal-footer pt-0" style="border: none;">
                                <button type="button" class="btn go-back-btn" data-bs-dismiss="modal">Go Back</button>
                                <button type="button" class="btn delete-btn" id="confirm-delete-btn">Yes, Delete</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- after deletion modal -->
                <div class="modal fade" id="postDelete" tabindex="-1" aria-labelledby="post-deleted" aria-hidden="true">
                    <div class="modal-dialog modal-md">
                        <div class="modal-content delete-message">
                            <div class="modal-header" style="border: none;">
                                <p class="modal-title" id="exampleModalLabel">Announcement deleted succesfully.</p>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                        </div>
                    </div>
                </div>



                <script src="admin.js"></script>
    </main>
    <!-- Body CDN links -->
    <?php include '../cdn/body.html'; ?>
</body>

</html>