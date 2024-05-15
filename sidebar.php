<nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block sidebar collapse">
    <div class="position-sticky pt-3">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a board-order="<?php echo $board['board_order']; ?>"
                    class="nav-link <?php echo $active = ($page == 'home') ? 'active' : '' ?>" href="./index.php">
                    <i class="fa fa-home"></i>
                    <span>Home</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $active = ($page == 'events') ? 'active' : '' ?>" href="./events.php">
                    <i class="fa fa-calendar"></i>
                    <span>Events</span>
                </a>
            </li>
            <?php
            if (isAdmin()) {
                ?>
                <li class="nav-item">
                    <a class="nav-link <?php echo $active = ($page == 'users') ? 'active' : '' ?>" href="./users.php">
                        <i class="fa fa-users"></i>
                        <span>Users</span>
                    </a>
                </li>
                <?php
            } ?>
            <li class="nav-item">
                <a class="nav-link <?php echo $active = ($page == 'messages') ? 'active' : '' ?>" href="./messages.php">
                    <i class="fa fa-comments"></i>
                    <span>Messages</span>
                    <?php
                    // echo countUnreadMessages();
                    ?>
                    <?php echo $unread = (countUnreadMessages() > 0) ? '<span class="badge bg-danger rounded-circle">' . countUnreadMessages() . '</span>' : '' ?>
                </a>
            </li>
        </ul>
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link add-link" href="#" data-bs-toggle="modal" data-bs-target="#addNoteModal">
                    <span><i class="fa fa-plus"></i> Add Note</span>
                </a>
            </li>
        </ul>
    </div>
</nav>