<?php
/**
 * Admin Panel
 * Simple admin interface for managing the site
 */

require_once __DIR__ . '/includes/header.php';

// Check if user is logged in and is admin
if (!isLoggedIn()) {
    setFlashMessage('error', 'You must be logged in to access the admin panel.');
    redirect('auth/login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
}

$db = new Database();
$user = getCurrentUser();

// Simple admin check (in production, you'd have a proper role system)
if (!isAdmin()) {
    setFlashMessage('error', 'Access denied. Admin privileges required.');
    redirect('dashboard.php');
}

// Get statistics
$stats = [];

try {
    // Total users
    $stmt = $db->prepare("SELECT COUNT(*) FROM users");
    $stmt->execute();
    $stats['users'] = $stmt->fetchColumn();

    // Total recipes
    $stmt = $db->prepare("SELECT COUNT(*) FROM recipes");
    $stmt->execute();
    $stats['recipes'] = $stmt->fetchColumn();

    // Total reviews
    $stmt = $db->prepare("SELECT COUNT(*) FROM reviews");
    $stmt->execute();
    $stats['reviews'] = $stmt->fetchColumn();

    // Total favorites
    $stmt = $db->prepare("SELECT COUNT(*) FROM favorites");
    $stmt->execute();
    $stats['favorites'] = $stmt->fetchColumn();

    // Recent users
    $stmt = $db->prepare("SELECT username, first_name, last_name, created_at FROM users ORDER BY created_at DESC LIMIT 5");
    $stmt->execute();
    $recentUsers = $stmt->fetchAll();

    // Recent recipes
    $stmt = $db->prepare("
        SELECT r.title, r.created_at, u.username, u.first_name, u.last_name 
        FROM recipes r 
        JOIN users u ON r.user_id = u.id 
        ORDER BY r.created_at DESC 
        LIMIT 5
    ");
$pending = [];
try {
    $stmt = $db->prepare("SELECT r.id, r.title, r.created_at, u.first_name, u.last_name FROM recipes r JOIN users u ON r.user_id = u.id WHERE r.approval_status = 'pending' ORDER BY r.created_at ASC LIMIT 20");
    $stmt->execute();
    $pending = $stmt->fetchAll();
} catch (Exception $e) { $pending = []; }

    $stmt->execute();
    $recentRecipes = $stmt->fetchAll();

    // Contact messages
    $stmt = $db->prepare("SELECT * FROM contact_messages ORDER BY created_at DESC LIMIT 10");
    $stmt->execute();
    $contactMessages = $stmt->fetchAll();

} catch (Exception $e) {
    error_log("Admin panel error: " . $e->getMessage());
    $stats = ['users' => 0, 'recipes' => 0, 'reviews' => 0, 'favorites' => 0];
    $recentUsers = [];
    $recentRecipes = [];
    $contactMessages = [];
}

$pageTitle = 'Admin Panel';
?>

<section class="admin-panel">
    <div class="container">
        <div class="admin-header">
            <h1>Admin Panel</h1>
            <p>Manage your TastyBook application</p>
        </div>

        <div class="admin-stats">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-content">
                    <h3><?php echo $stats['users']; ?></h3>
                    <p>Total Users</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-book"></i>
                </div>
                <div class="stat-content">
                    <h3><?php echo $stats['recipes']; ?></h3>
                    <p>Total Recipes</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-star"></i>
                </div>
                <div class="stat-content">
                    <h3><?php echo $stats['reviews']; ?></h3>
                    <p>Total Reviews</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-heart"></i>
                </div>
                <div class="stat-content">
                    <h3><?php echo $stats['favorites']; ?></h3>
                    <p>Total Favorites</p>
                </div>
            </div>
        </div>

        <div class="admin-content">
            <div class="admin-section">
                <h2>Users</h2>
                <div class="admin-table">
                    <?php
                    try {
                        $stmt = $db->prepare("SELECT id, username, first_name, last_name, email, role, points_total, created_at FROM users ORDER BY created_at DESC LIMIT 50");
                        $stmt->execute();
                        $users = $stmt->fetchAll();
                    } catch (Exception $e) { $users = []; }
                    ?>
                    <?php if (!empty($users)): ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Total Points</th>
                                    <th>Joined</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $row): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?> (<?php echo htmlspecialchars($row['username']); ?>)</td>
                                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                                        <td><?php echo htmlspecialchars(ucfirst($row['role'])); ?></td>
                                        <td><?php echo (int)$row['points_total']; ?></td>
                                        <td><?php echo date('M j, Y', strtotime($row['created_at'])); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p>No users found.</p>
                    <?php endif; ?>
                </div>
            </div>
            <div class="admin-section">
                <h2>Pending Recipe Approvals</h2>
                <div class="admin-table">
                    <?php if (!empty($pending)): ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>Recipe</th>
                                    <th>Author</th>
                                    <th>Submitted</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($pending as $p): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($p['title']); ?></td>
                                        <td><?php echo htmlspecialchars($p['first_name'] . ' ' . $p['last_name']); ?></td>
                                        <td><?php echo date('M j, Y H:i', strtotime($p['created_at'])); ?></td>
                                        <td>
                                            <form method="post" action="recipes/edit-recipe.php" style="display:inline;">
                                                <input type="hidden" name="action" value="admin_approve">
                                                <input type="hidden" name="recipe_id" value="<?php echo $p['id']; ?>">
                                                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                                <button class="btn btn-primary">Approve</button>
                                            </form>
                                            <form method="post" action="recipes/edit-recipe.php" style="display:inline; margin-left: .5rem;">
                                                <input type="hidden" name="action" value="admin_reject">
                                                <input type="hidden" name="recipe_id" value="<?php echo $p['id']; ?>">
                                                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                                <button class="btn btn-outline">Reject</button>
                                            </form>
                                            <form method="post" action="recipes/edit-recipe.php" style="display:inline; margin-left: .5rem;">
                                                <input type="hidden" name="action" value="admin_top">
                                                <input type="hidden" name="recipe_id" value="<?php echo $p['id']; ?>">
                                                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                                <button class="btn btn-secondary">Mark Top</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p>No pending recipes.</p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="admin-section">
                <h2>Mark Recipes as Good</h2>
                <div class="admin-table">
                    <?php
                    try {
                        $stmt = $db->prepare("SELECT r.id, r.title, r.created_at, r.quality, u.first_name, u.last_name FROM recipes r JOIN users u ON r.user_id = u.id WHERE r.approval_status = 'approved' ORDER BY r.created_at DESC LIMIT 20");
                        $stmt->execute();
                        $approved = $stmt->fetchAll();
                    } catch (Exception $e) { $approved = []; }
                    ?>
                    <?php if (!empty($approved)): ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>Recipe</th>
                                    <th>Author</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($approved as $r): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($r['title']); ?></td>
                                        <td><?php echo htmlspecialchars($r['first_name'] . ' ' . $r['last_name']); ?></td>
                                        <td><?php echo htmlspecialchars(ucfirst($r['quality'])); ?></td>
                                        <td>
                                            <form method="post" action="recipes/edit-recipe.php" style="display:inline;">
                                                <input type="hidden" name="action" value="admin_good">
                                                <input type="hidden" name="recipe_id" value="<?php echo $r['id']; ?>">
                                                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                                <button class="btn btn-secondary">Mark Good</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p>No approved recipes yet.</p>
                    <?php endif; ?>
                </div>
            </div>
            <div class="admin-section">
                <h2>Recent Users</h2>
                <div class="admin-table">
                    <?php if (!empty($recentUsers)): ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>Username</th>
                                    <th>Name</th>
                                    <th>Joined</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentUsers as $user): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($user['username']); ?></td>
                                        <td><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></td>
                                        <td><?php echo date('M j, Y', strtotime($user['created_at'])); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p>No users found.</p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="admin-section">
                <h2>Recent Recipes</h2>
                <div class="admin-table">
                    <?php if (!empty($recentRecipes)): ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>Recipe</th>
                                    <th>Author</th>
                                    <th>Created</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentRecipes as $recipe): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($recipe['title']); ?></td>
                                        <td><?php echo htmlspecialchars($recipe['first_name'] . ' ' . $recipe['last_name']); ?></td>
                                        <td><?php echo date('M j, Y', strtotime($recipe['created_at'])); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p>No recipes found.</p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="admin-section">
                <h2>Contact Messages</h2>
                <div class="admin-table">
                    <?php if (!empty($contactMessages)): ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Subject</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($contactMessages as $message): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($message['name']); ?></td>
                                        <td><?php echo htmlspecialchars($message['email']); ?></td>
                                        <td><?php echo htmlspecialchars($message['subject'] ?: 'No Subject'); ?></td>
                                        <td><?php echo date('M j, Y', strtotime($message['created_at'])); ?></td>
                                        <td>
                                            <span class="status-badge <?php echo $message['status']; ?>">
                                                <?php echo ucfirst($message['status']); ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p>No contact messages found.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="admin-actions">
            <a href="dashboard.php" class="btn btn-outline">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
            <a href="setup.php" class="btn btn-primary">
                <i class="fas fa-database"></i> Database Setup
            </a>
        </div>
    </div>
</section>

<style>
.admin-panel {
    padding: 2rem 0;
    min-height: 80vh;
}

.admin-header {
    text-align: center;
    margin-bottom: 3rem;
}

.admin-header h1 {
    color: #333;
    margin-bottom: 0.5rem;
    font-size: 2.5rem;
}

.admin-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1.5rem;
    margin-bottom: 3rem;
}

.admin-content {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
    gap: 2rem;
    margin-bottom: 3rem;
}

.admin-section {
    background: white;
    padding: 2rem;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

.admin-section h2 {
    color: #333;
    margin-bottom: 1.5rem;
    font-size: 1.5rem;
}

.admin-table {
    overflow-x: auto;
}

.admin-table table {
    width: 100%;
    border-collapse: collapse;
}

.admin-table th,
.admin-table td {
    padding: 0.75rem;
    text-align: left;
    border-bottom: 1px solid #e1e5e9;
}

.admin-table th {
    background: #f8f9fa;
    font-weight: 600;
    color: #333;
}

.admin-table tr:hover {
    background: #f8f9fa;
}

.status-badge {
    padding: 0.25rem 0.75rem;
    border-radius: 15px;
    font-size: 0.8rem;
    font-weight: 500;
}

.status-badge.new {
    background: #e3f2fd;
    color: #1976d2;
}

.status-badge.read {
    background: #e8f5e8;
    color: #2e7d32;
}

.status-badge.replied {
    background: #fff3e0;
    color: #f57c00;
}

.status-badge.closed {
    background: #ffebee;
    color: #d32f2f;
}

.admin-actions {
    text-align: center;
    display: flex;
    gap: 1rem;
    justify-content: center;
    flex-wrap: wrap;
}

@media (max-width: 768px) {
    .admin-content {
        grid-template-columns: 1fr;
    }
    
    .admin-actions {
        flex-direction: column;
        align-items: center;
    }
    
    .admin-actions .btn {
        width: 100%;
        max-width: 200px;
    }
}
</style>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
