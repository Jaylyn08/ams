<?php
// Shared sidebar navigation.
// Expects includes/auth.php to have already run, providing:
//   $isAdmin (bool) and $currentUserName (string).
$currentPage = basename($_SERVER['SCRIPT_NAME']);

// Links every logged-in user sees.
$sidebarLinks = [
    ['href' => 'index.php', 'label' => 'Home'],
    ['href' => 'student.php', 'label' => 'Student'],
    ['href' => 'report.php', 'label' => 'Report'],
];
// Admin-only links, appended so they always render after the ones above.
if (!empty($isAdmin)) {
    $sidebarLinks[] = ['href' => 'admin.php', 'label' => 'Users'];
    $sidebarLinks[] = ['href' => 'section.php', 'label' => 'Sections'];
    $sidebarLinks[] = ['href' => 'grade.php', 'label' => 'Grades'];
}
?>
<button type="button" id="sidebarToggle" class="sidebar-toggle" aria-label="Toggle navigation" aria-expanded="false" aria-controls="appSidebar">&#9776;</button>
<div id="sidebarOverlay" class="sidebar-overlay"></div>
<nav id="appSidebar" class="sidebar">
    <div class="sidebar-brand">
        <img src="img/pmanalologo.jpg" alt="AMS logo" class="sidebar-logo">
        <span>AMS</span>
    </div>
    <div class="sidebar-links">
        <?php foreach ($sidebarLinks as $link): ?>
            <!-- Highlights the link matching the page currently being viewed -->
            <a class="sidebar-link<?= $currentPage === $link['href'] ? ' active' : '' ?>" href="<?= htmlspecialchars($link['href']) ?>"><?= htmlspecialchars($link['label']) ?></a>
        <?php endforeach; ?>
    </div>
    <div class="sidebar-footer">
        <?php if ($currentUserName !== ''): ?>
            <div class="sidebar-user"><?= htmlspecialchars($currentUserName) ?></div>
        <?php endif; ?>
        <a class="sidebar-link sidebar-logout" href="logout.php">Logout</a>
    </div>
</nav>
