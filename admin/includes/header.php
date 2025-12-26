<header class="admin-header">
    <div class="header-left">
        <!-- ADD NEW -->
          <div class="dropdown">
            <button class="btn primary" onclick="toggleDropdown('addDropdown')">
                <i class="fa-solid fa-plus"></i>
                Add New
            </button>

            <div class="dropdown-menu right" id="addDropdown">
                <a href="#" onclick="loadPage('users/add_admin')">
                    <i class="fa-solid fa-user-shield"></i>
                   Add Admin
                </a>
                <a href="#" onclick="loadPage('users/add_technician')">
                    <i class="fa-solid fa-screwdriver-wrench"></i>
                    Add Technician
                </a>
            </div>
        </div>
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