<header class="admin-header">
    <div class="header-left">
        <!-- USER -->
        <div class="dropdown">
            <button class="user-btn" onclick="toggleDropdown('userDropdown')">
                <img src="../assests/uploads/avatar.jpg" class="avatar">
                
            </button>

            <div class="dropdown-menu right" id="userDropdown">
                <a href="../index.php" target="_blank">
                    <i class="fa-solid fa-house"></i>
                    Home page
                </a>
                <a href="../auth/logout.php" class="danger">
                    <i class="fa-solid fa-right-from-bracket"></i>
                    Logout
                </a>
            </div>
        </div>
    </div>
</header>