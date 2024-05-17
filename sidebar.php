<nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block sidebar collapse">
    <div class="position-sticky pt-3">
        <?php
        if (isLoggedIn()) {
            ?>
            <h5 class="text-white fw-bold mx-auto w-100 text-center my-3 d-md-none d-block">
                <span class="lang-en">Hi, <?php echo $_SESSION['name']; ?></span>
                <span class="lang-de">Hallo, <?php echo $_SESSION['name']; ?></span>
            </h5>
            <?php
        }
        ?>
        <ul class="nav flex-column">
            <li class="nav-item">
                <a board-order="<?php echo $board['board_order']; ?>"
                    class="nav-link <?php echo $active = ($page == 'events') ? 'active' : '' ?>" href="./index.php">
                    <i class="fa fa-home"></i>
                    <span>
                        <span class="lang-en">Home</span>
                        <span class="lang-de">Startseite</span>
                    </span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $active = ($page == 'home') ? 'active' : '' ?>" href="./calendar.php">
                    <i class="fa fa-calendar"></i>
                    <span>
                        <span class="lang-en">Calendar</span>
                        <span class="lang-de">Kalender</span>
                    </span>
                </a>
            </li>
            <?php
            if (isAdmin()) {
                ?>
                <li class="nav-item">
                    <a class="nav-link <?php echo $active = ($page == 'users') ? 'active' : '' ?>" href="./users.php">
                        <i class="fa fa-users"></i>
                        <span>
                            <span class="lang-en">Users</span>
                            <span class="lang-de">Benutzer</span>
                        </span>
                    </a>
                </li>
                <?php
            } ?>
            <li class="nav-item">
                <a class="nav-link <?php echo $active = ($page == 'messages') ? 'active' : '' ?>" href="./inbox.php">
                    <i class="fa fa-comments"></i>
                    <span>
                        <span class="lang-en">Messages</span>
                        <span class="lang-de">Nachrichten</span>
                    </span>
                </a>
            </li>
        </ul>
        <div class="navbar-nav flex-column gap-1 align-items-center d-md-none d-flex">
            <div class="nav-item text-nowrap">
                <div class="dropdown">
                    <button class="btn btn-secondary dropdown-toggle" type="button" id="languageDropdown"
                        data-bs-toggle="dropdown" aria-expanded="false"><span class="lang-en"><img
                                src="./assets/img/en.png" class="flag-icon" /></span><span class="lang-de"><img
                                src="./assets/img/de.png" class="flag-icon" /></span> Language </button>
                    <ul class="dropdown-menu lang-menu" aria-labelledby="languageDropdown">
                        <li><button class="dropdown-item" onclick="changeLanguage('en')">English</button></li>
                        <li><button class="dropdown-item" onclick="changeLanguage('de')">German</button></li>
                    </ul>
                </div>
            </div>
            <div class="nav-item text-nowrap">
                <?php
                if (isLoggedIn()) {
                    ?>
                    <a class="nav-link px-3" href="./logout.php">
                        <span class="lang-en">Logout</span>
                        <span class="lang-de">Ausloggen</span>
                    </a>
                    <?php
                } else {
                    ?>
                    <a class="nav-link px-3" href="./login.php">
                        <span class="lang-en">Login</span>
                        <span class="lang-de">Anmelden</span>
                    </a>
                    <?php
                }
                ?>
            </div>
        </div>
    </div>
</nav>