<?php



require_once 'config/database.php';
require_once 'functions/task_functions.php';


$success_message = '';
$error_message = '';
$validation_errors = [];


$pdo = getConnection();


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    
    if (isset($_POST['action']) && $_POST['action'] === 'create_task') {
        
        
        $task_data = [
            'title' => $_POST['title'] ?? '',
            'description' => $_POST['description'] ?? '',
            'status' => $_POST['status'] ?? 'pending',
            'priority' => $_POST['priority'] ?? 'medium',
            'due_date' => $_POST['due_date'] ?? '',
            'category_id' => $_POST['category_id'] ?? null
        ];
        
        
        $validation_errors = validateTaskData($task_data);
        
        
        if (empty($validation_errors)) {
            $result = createTask(
                $pdo,
                $task_data['title'],
                $task_data['description'],
                $task_data['status'],
                $task_data['priority'],
                $task_data['due_date'] ?: null,
                $task_data['category_id'] ?: null
            );
            
            if ($result) {
                $success_message = "Task created successfully!";
                
                $task_data = [];
            } else {
                $error_message = "Failed to create task. Please try again.";
            }
        }
    }
    
  
    if (isset($_POST['action']) && $_POST['action'] === 'update_status') {
        $task_id = $_POST['task_id'] ?? 0;
        $new_status = $_POST['new_status'] ?? '';
        
        if ($task_id && $new_status) {
            $result = updateTaskStatus($pdo, $task_id, $new_status);
            
            if ($result) {
                $success_message = "Task status updated successfully!";
            } else {
                $error_message = "Failed to update task status.";
            }
        }
    }
    
  
    if (isset($_POST['action']) && $_POST['action'] === 'delete_task') {
        $task_id = $_POST['task_id'] ?? 0;
        
        if ($task_id) {
            $result = deleteTask($pdo, $task_id);
            
            if ($result) {
                $success_message = "Task deleted successfully!";
            } else {
                $error_message = "Failed to delete task.";
            }
        }
    }
}


$filter_status = $_GET['status'] ?? null;

$tasks = getAllTasks($pdo, $filter_status);
$categories = getAllCategories($pdo);
$stats = getTaskStats($pdo);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Manager</title>
    <link rel="stylesheet" href="style.css" />
</head>
<body>
    <div class="container">
        <header>
            <div class="header-content">
                <h1>Task Manager</h1>
            </div>
        </header>

      
        <div class="stats">
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['pending']; ?></div>
                <div>Pending Tasks</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['in_progress']; ?></div>
                <div>In Progress</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['completed']; ?></div>
                <div>Completed</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo array_sum($stats); ?></div>
                <div>Total Tasks</div>
            </div>
        </div>

        
        <?php if (!empty($success_message)): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success_message); ?></div>
        <?php endif; ?>

        <?php if (!empty($error_message)): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>

        <?php if (!empty($validation_errors)): ?>
            <div class="alert alert-error">
                <strong>Please fix the following errors:</strong>
                <ul class="error-list">
                    <?php foreach ($validation_errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <div class="main-content">
            <div class="sidebar">
                <h3>Add New Task</h3>
                <form method="POST">
                    <input type="hidden" name="action" value="create_task">
                    
                    <div class="form-group">
                        <label for="title">Task Title *</label>
                        <input type="text" 
                               name="title" 
                               id="title"
                               value="<?php echo htmlspecialchars($task_data['title'] ?? ''); ?>"
                               required>
                    </div>
                    
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea name="description" 
                                  id="description" 
                                  rows="3"><?php echo htmlspecialchars($task_data['description'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="category_id">Category</label>
                        <select name="category_id" id="category_id">
                            <option value="">Select Category</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category['id']; ?>"
                                        <?php echo (isset($task_data['category_id']) && $task_data['category_id'] == $category['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($category['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="priority">Priority</label>
                        <select name="priority" id="priority">
                            <option value="low" <?php echo (isset($task_data['priority']) && $task_data['priority'] === 'low') ? 'selected' : ''; ?>>Low</option>
                            <option value="medium" <?php echo (!isset($task_data['priority']) || $task_data['priority'] === 'medium') ? 'selected' : ''; ?>>Medium</option>
                            <option value="high" <?php echo (isset($task_data['priority']) && $task_data['priority'] === 'high') ? 'selected' : ''; ?>>High</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="status">Status</label>
                        <select name="status" id="status">
                            <option value="pending" <?php echo (!isset($task_data['status']) || $task_data['status'] === 'pending') ? 'selected' : ''; ?>>Pending</option>
                            <option value="in_progress" <?php echo (isset($task_data['status']) && $task_data['status'] === 'in_progress') ? 'selected' : ''; ?>>In Progress</option>
                            <option value="completed" <?php echo (isset($task_data['status']) && $task_data['status'] === 'completed') ? 'selected' : ''; ?>>Completed</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="due_date">Due Date</label>
                        <input type="date" 
                               name="due_date" 
                               id="due_date"
                               value="<?php echo htmlspecialchars($task_data['due_date'] ?? ''); ?>">
                    </div>
                    
                    <button type="submit">Add Task</button>
                </form>
            </div>

            
            <div class="tasks-section">
                <h2>Tasks</h2>
                
             
                <div class="filter-tabs">
                    <a href="?" class="filter-tab <?php echo !$filter_status ? 'active' : ''; ?>">
                        All Tasks (<?php echo array_sum($stats); ?>)
                    </a>
                    <a href="?status=pending" class="filter-tab <?php echo $filter_status === 'pending' ? 'active' : ''; ?>">
                        Pending (<?php echo $stats['pending']; ?>)
                    </a>
                    <a href="?status=in_progress" class="filter-tab <?php echo $filter_status === 'in_progress' ? 'active' : ''; ?>">
                        In Progress (<?php echo $stats['in_progress']; ?>)
                    </a>
                    <a href="?status=completed" class="filter-tab <?php echo $filter_status === 'completed' ? 'active' : ''; ?>">
                        Completed (<?php echo $stats['completed']; ?>)
                    </a>
                </div>

           
                <div class="tasks-list">
                    <?php if (empty($tasks)): ?>
                        <p>No tasks found. Create your first task using the form on the left!</p>
                    <?php else: ?>
                        <?php foreach ($tasks as $task): ?>
                            <div class="task-card">
                                <div class="task-header">
                                    <div>
                                        <div class="task-title"><?php echo htmlspecialchars($task['title']); ?></div>
                                        <div class="task-meta">
                                            <span class="priority <?php echo getPriorityClass($task['priority']); ?>">
                                                <?php echo ucfirst($task['priority']); ?> Priority
                                            </span>
                                            <span class="status <?php echo getStatusClass($task['status']); ?>">
                                                <?php echo formatStatus($task['status']); ?>
                                            </span>
                                            <?php if ($task['category_name']): ?>
                                                <span class="category-tag" style="background-color: <?php echo $task['category_color']; ?>">
                                                    <?php echo htmlspecialchars($task['category_name']); ?>
                                                </span>
                                            <?php endif; ?>
                                            <?php if ($task['due_date']): ?>
                                                <span>Due: <?php echo formatDate($task['due_date']); ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                
                                <?php if (!empty($task['description'])): ?>
                                    <p><?php echo nl2br(htmlspecialchars($task['description'])); ?></p>
                                <?php endif; ?>
                                
                                <div class="task-actions">
                                    <?php if ($task['status'] !== 'completed'): ?>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="action" value="update_status">
                                            <input type="hidden" name="task_id" value="<?php echo $task['id']; ?>">
                                            <input type="hidden" name="new_status" value="completed">
                                            <button type="submit" class="btn-small btn-success">
                                                ✓ Mark Complete
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                    
                                    <?php if ($task['status'] === 'pending'): ?>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="action" value="update_status">
                                            <input type="hidden" name="task_id" value="<?php echo $task['id']; ?>">
                                            <input type="hidden" name="new_status" value="in_progress">
                                            <button type="submit" class="btn-small">
                                                ▶ Start
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                    
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="action" value="delete_task">
                                        <input type="hidden" name="task_id" value="<?php echo $task['id']; ?>">
                                        <button type="submit" 
                                                class="btn-small btn-danger" 
                                                onclick="return confirm('Are you sure you want to delete this task?')">
                                            🗑 Delete
                                        </button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        
        document.addEventListener('DOMContentLoaded', function() {
           
            const successAlert = document.querySelector('.alert-success');
            if (successAlert) {
                setTimeout(() => {
                    successAlert.style.opacity = '0';
                    setTimeout(() => {
                        successAlert.remove();
                    }, 500);
                }, 3000);
            }
        });
    </script>
</body>
</html>