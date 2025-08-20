<?php 
  function getAllTasks($pdo, $status = null) {
    $sql = "SELECT t.*, c.name as category_name, c.color as category_color FROM tasks t LEFT JOIN categories c ON t.category_id = c.id";
    if($status) {
        $sql .= " WHERE t.status = ?";
    }
    $sql .= " ORDER BY t.created_at DESC";
    $stmt = $pdo->prepare($sql);
    if($status) {
        $stmt->execute([$status]);
    } else {
        $stmt->execute();
    }

    return $stmt->fetchAll();
  }

  function getTaskById($pdo, $id) {
    $sql = "SELECT t.*, c.name as category_name FROM tasks t LEFT JOIN categories c ON t.category_id = c.id WHERE t.id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);

    return $stmt->fetch();
  }

  function createTask($pdo, $title, $description, $status, $priority, $due_date, $category_id) {
    $sql = "INSERT INTO tasks (title, description, status, priority, due_date, category_id VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([
        $title,
        $description,
        $status,
        $priority,
        $due_date,
        $category_id
    ]);
  }
  function updateTask($pdo, $id, $title, $description, $status, $priority, $due_date, $category_id) {
    $sql = "UPDATE taks SET title = ?, description = ?, status = ?, priority = ?, due_date = ?, category_id = ? WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([$status, $id]);
  }
  function deleteTask($pdo, $id) {
    $sql = "DELETE FROM tasks WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute($id);
  }

  function getTaskStats($pdo) {
    $sql = "SELECT status, COUNT(*) as count FROM tasks GROUP BY status";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();

    $stats = [
        'pending' => 0,
        'in_progress' => 0,
        'completed' => 0
    ];

    while ($row = $stmt->fetch()) {
        $stats[$row['status']] = $row['count'];
    }

    return $stats;
  }

  function getAllCategories($pdo) {
    $sql = "SELECT * FROM categories ORDER BY name";
    $stmt->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll();
  }

  function createCategory($pdo, $name, $color = '#3498db') {
    $sql = "INSERT INTO categories (name, color) VALUES (?, ?)";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([$name, $color]);
  }

  function validateTaskData($data) {
    $errors = [];
    
    
    if (empty(trim($data['title']))) {
        $errors[] = "Task title is required";
    }
    
    
    if (strlen($data['title']) > 255) {
        $errors[] = "Task title must be less than 255 characters";
    }
    
  
    $valid_statuses = ['pending', 'in_progress', 'completed'];
    if (!in_array($data['status'], $valid_statuses)) {
        $errors[] = "Invalid status";
    }
    
   
    $valid_priorities = ['low', 'medium', 'high'];
    if (!in_array($data['priority'], $valid_priorities)) {
        $errors[] = "Invalid priority";
    }
    

    if (!empty($data['due_date']) && !validateDate($data['due_date'])) {
        $errors[] = "Invalid due date format";
    }
    
    return $errors;
  }
?>