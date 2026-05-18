<!-- Topbar -->
<nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">

    <!-- Sidebar Toggle -->
    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
        <i class="fa fa-bars"></i>
    </button>

    <h5 class="mb-0 text-gray-800">
        Hệ thống quản lý thuê xe
    </h5>

    <ul class="navbar-nav ml-auto">

        <li class="nav-item dropdown no-arrow">
            <a class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown">
                <span class="mr-2 d-none d-lg-inline text-gray-600 small">
                    <?= $_SESSION['name'] ?? 'Admin' ?>
                </span>

                <img class="img-profile rounded-circle"
                    src="https://ui-avatars.com/api/?name=Admin">
            </a>
        </li>

    </ul>

</nav>
<!-- End Topbar -->