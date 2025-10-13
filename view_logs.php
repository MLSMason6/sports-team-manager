<?php 
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
if ($_SESSION['role'] !== 'superadmin') {
    die("Access denied. Only Super Admins can view logs.");
}

$host = "localhost";
$dbname = "sports_manager";
$username = "root";
$password = "";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $users = $pdo->query("SELECT user_id, username FROM Users ORDER BY username ASC")->fetchAll(PDO::FETCH_ASSOC);

    // --- Filters --- 
    $conditions = [];
    $params = [];

    if (!empty($_GET['user_id'])) {
         $conditions[] = "a.user_id = :user_id";
        $params['user_id'] = $_GET['user_id'];
    }
    if (!empty($_GET['action_keyword'])) {
        $conditions[] = "(a.action LIKE :keyword OR a.details LIKE :keyword)";
        $params['keyword'] = "%" . $_GET['action_keyword'] . "%";
    }
    if (!empty($_GET['start_date'])) {
        $conditions[] = "DATE(a.created_at) >= :start_date";
        $params['start_date'] = $_GET['start_date'];
    }
    if (!empty($_GET['end_date'])) { 
        $conditions[] = "DATE(a.created_at) <= :end_date";
        $params['end_date'] = $_GET['end_date'];
    }

    $whereClause = count($conditions) ? "WHERE " . implode(" AND ", $conditions) : "";

    // --- Pagination --- 
    $perPage = 50; 
    $page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
    if ($page < 1) $page = 1;
    $offset = ($page - 1) * $perPage;

    $countSql = "SELECT COUNT(*) FROM AuditLog a JOIN Users u ON a.user_id = u.user_id $whereClause";
    $countStmt = $pdo->prepare($countSql);
    $countStmt->execute($params);
    $totalLogs = $countStmt->fetchColumn();
    $totalPages = ceil($totalLogs / $perPage);

    $sql = "SELECT a.log_id, u.username, a.action, a.details, a.created_at
            FROM AuditLog a 
            JOIN Users u ON a.user_id = u.user_id
            $whereClause 
            ORDER BY a.created_at DESC 
            LIMIT :limit OFFSET :offset";
    
    $stmt = $pdo->prepare($sql);
    foreach($params as $key => &$value) { $stmt->bindParam(":$key", $value);}
    $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // --- CSV Export ---
    if (isset($_GET['export']) && $_GET['export'] === 'csv') { 
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="audit_logs.csv"');
        $output = fopen('php://output', 'w');
        fputcsv($output, ['Log ID', 'User', 'Action', 'Details', 'Date']);
        foreach ($logs as $log) {
            fputcsv($output, [$log['log_id'], $log['username'], $log['action'], $log['details'], $log['created_at']]); 
        } 
        fclose($output); 
        exit();
    }

} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Audit Logs - Filters & Exports</title>
    <style>
            body { font-family: Arial, sans-serif; background: #f7f7f7; padding: 20px; }
            h2 { color: #2c3e50; }
            form { background: white; padding: 15px; border-radius: 8px; margin-bottom: 20px; }
            label { font-weight: bold; margin-right: 10px; }
            input, select { margin-right: 15px; }
            button { cursor: pointer; }
            table { border-collapse: collapse; width: 100%; background: white; }
            th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
            th { background-color: #2c3e50; color: white; }
            tr:nth-child(even) { background: #f9f9f9; }
            a { color: #2980b9; text-decoration: none; }
            a:hover { text-decoration: underline; }
            .pagination { margin-top: 15px; text-align: center; }
                    .pagination a, .pagination span {
                        display: inline-block;
                        margin: 0 5px;
                        padding: 6px 12px;
                        border-radius: 5px;
                        background: #2c3e50;
                        color: white;
                        text-decoration: none;
                    }
                    .pagination span { background: gray; }
                    /* Modal styles */
                            .modal {
                                display: none; 
                                position: fixed; 
                                z-index: 1000; 
                                left: 0; top: 0;
                                width: 100%; height: 100%; 
                                overflow: auto; 
                                background-color: rgba(0,0,0,0.6);
                            }
                            .modal-content {
                                background-color: #fff;
                                margin: 10% auto; 
                                padding: 20px; 
                                border-radius: 8px; 
                                width: 60%;
                                box-shadow: 0 2px 10px rgba(0,0,0,0.3);
                            }
                            .close-btn {
                                float: right; 
                                font-size: 20px; 
                                cursor: pointer; 
                                color: #2c3e50;
                            }
        </style>
</head>
<body>
    <h2>Audit Logs (Filter, Export, Pagination)</h2>

    <form method="GET">
            <label>User:</label>
            <select name="user_id">
                <option value="">All Users</option>
                <?php foreach ($users as $user): ?>
                    <option value="<?= $user['user_id'] ?>" <?= (isset($_GET['user_id']) && $_GET['user_id'] == $user['user_id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($user['username']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
    
            <label>Keyword:</label>
            <input type="text" name="action_keyword" value="<?= $_GET['action_keyword'] ?? '' ?>" placeholder="Search action or details">
    
            <label>From:</label>
            <input type="date" name="start_date" value="<?= $_GET['start_date'] ?? '' ?>">
    
            <label>To:</label>
            <input type="date" name="end_date" value="<?= $_GET['end_date'] ?? '' ?>">
    
            <button type="submit">Apply Filters</button>
            <a href="view_logs.php">Reset</a>
            <button type="submit" name="export" value="csv">Export CSV</button>
        </form>
    <table>
        <tr><th>ID</th><th>User</th><th>Action</th><th>Details</th><th>Date</th></tr>
        <?php if (empty($logs)): ?>
            <tr><td colspan="5" style="text-align:center;">No logs found for selected filters.</td></tr>
        <?php else: ?>
            <?php foreach ($logs as $log): ?>
            <tr>
                <td><?= $log['log_id'] ?></td>
                <td><?= htmlspecialchars($log['username']) ?></td>
                <td><?= htmlspecialchars($log['action']) ?></td>
                <td><?= htmlspecialchars(substr($log['details'], 0, 40)) ?><?= strlen($log['details']) > 40 ? '...' : '' ?></td>
                <td><?= $log['created_at'] ?></td>
                <td><a href="#" class="view-details" 
                                    data-id="<?= $log['log_id'] ?>"
                                    data-user="<?= htmlspecialchars($log['username']) ?>"
                                    data-action="<?= htmlspecialchars($log['action']) ?>"
                                    data-details="<?= htmlspecialchars($log['details']) ?>"
                                    data-date="<?= $log['created_at'] ?>"
                                >👁 View</a></td>
            </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </table>

    <!-- Pagination Controls -->
       <?php if ($totalPages > 1): ?>
           <div class="pagination">
               <?php if ($page > 1): ?>
                   <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>">⬅ Prev</a>
               <?php endif; ?>
               <span>Page <?= $page ?> of <?= $totalPages ?></span>
               <?php if ($page < $totalPages): ?>
                   <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>">Next ➡</a>
               <?php endif; ?>
           </div>
       <?php endif; ?> 
        
    <p><a href="dashboard.php">⬅ Back to Dashboard</a></p>

    <!-- Modal -->
    <div class="modal-content">
        <span class="close-btn">&times;</span>
        <h3>Log Details</h3>
        <p><strong>ID:</strong> <span id="modalId"></span></p>
        <p><strong>User:</strong> <span id="modalUser"></span></p>
        <p><strong>Action:</strong> <span id="modalAction"></span></p>
        <p><strong>Details (editable):</strong></p>
        <textarea id="modalDetails" style="width:100%; height:120px; padding:10px; border-radius:5px; border:1px solid #ccc;"></textarea>
        <p><strong>Date:</strong> <span id="modalDate"></span></p>
        <button id="saveChanges" style="margin-top:10px; background:#27ae60; color:white; border:none; padding:8px 15px; border-radius:5px; cursor:pointer;">💾 Save Changes</button>
        <p id="saveMessage" style="color:green; margin-top:8px;"></p>
        
        <hr>
        
        <h3 style="margin-top:20px;">🗒 Notes</h3>
        
        <div id="notesList" style="background:#eef1f5; padding:10px; border-radius:8px; max-height:200px; overflow-y:auto; border:1px solid #ccc;">
            <p style="color:#666;">Loading notes...</p>
        </div>
        
        <div style="margin-top:10px; display:flex; flex-direction:column; gap:8px;">
            <textarea id="newNote" placeholder="Add a note..." style="width:100%; height:80px; padding:10px; border-radius:6px; border:1px solid #bbb; resize:none;"></textarea>
            <button id="addNoteBtn" style="align-self:flex-end; background:#0078d4; color:white; border:none; padding:8px 14px; border-radius:6px; cursor:pointer; font-weight:bold;">➕ Add Note</button>
            <p id="noteMsg" style="color:green; margin:0;"></p>
        </div>
    </div> 
    
    <script>
    const modal = document.getElementById('detailsModal');
    const closeBtn = document.querySelector('.close-btn');
    const detailLinks = document.querySelectorAll('.view-details');
    const saveBtn = document.getElementById('saveChanges');
    const saveMsg = document.getElementById('saveMessage');
    
    let activeRow = null; // track which row was opened
    
    detailLinks.forEach(link => {
        link.addEventListener('click', (e) => {
            e.preventDefault();
    
            // Remember the row being viewed
            activeRow = link.closest('tr');
    
            document.getElementById('modalId').textContent = link.dataset.id;
            document.getElementById('modalUser').textContent = link.dataset.user;
            document.getElementById('modalAction').textContent = link.dataset.action;
            document.getElementById('modalDetails').value = link.dataset.details;
            document.getElementById('modalDate').textContent = link.dataset.date;
            saveMsg.textContent = "";
            modal.style.display = 'block';
        });
    });
    
    closeBtn.onclick = () => modal.style.display = 'none';
    window.onclick = (e) => { if (e.target === modal) modal.style.display = 'none'; };
    
    // 💾 Save edits via AJAX and auto-update table
    saveBtn.addEventListener('click', () => {
        const logId = document.getElementById('modalId').textContent;
        const newDetails = document.getElementById('modalDetails').value;
    
        fetch('update_log.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: new URLSearchParams({
                log_id: logId,
                details: newDetails
            })
        })
        .then(res => res.text())
        .then(response => {
            if (response.includes("success")) {
                saveMsg.style.color = "green";
                saveMsg.textContent = "✅ Details updated successfully!";
    
                // Update visible details cell in the table
                if (activeRow) {
                    const detailsCell = activeRow.querySelector('td:nth-child(4)');
                    const viewLink = activeRow.querySelector('.view-details');
                    detailsCell.textContent = newDetails.length > 40 ? newDetails.substring(0, 40) + '...' : newDetails;
                    viewLink.dataset.details = newDetails;
                }
    
                setTimeout(() => {
                    saveMsg.textContent = "";
                    modal.style.display = 'none';
                }, 1200);
            } else {
                saveMsg.style.color = "red";
                saveMsg.textContent = "❌ Failed to update log.";
            }
        })
        .catch(() => {
            saveMsg.style.color = "red";
            saveMsg.textContent = "⚠️ Error saving changes.";
        });
    });

    let activeLogId = null;
    let lastNoteCount = 0;
    let notePolling = null;
    
    // Load notes (used for first open + refreshes)
    function loadNotes(logId, silent = false) {
        const notesDiv = document.getElementById('notesList');
        if (!silent) notesDiv.innerHTML = "<p style='color:#666;'>Loading notes...</p>";
    
        fetch('get_notes.php?log_id=' + logId)
        .then(res => res.json())
        .then(data => {
            // If number of notes changed, update UI
            if (data.length !== lastNoteCount || !silent) {
                lastNoteCount = data.length;
    
                if (data.length === 0) {
                    notesDiv.innerHTML = "<p style='color:#999;'>No notes yet.</p>";
                    return;
                }
    
                notesDiv.innerHTML = data.map(note => {
                    const initials = note.username.charAt(0).toUpperCase();
                    const isOwn = note.username === "<?=$_SESSION['username'] ?? ''?>";
                    const bubbleColor = isOwn ? "#0078d4" : "#e1e9f2";
                    const textColor = isOwn ? "white" : "#333";
                    const align = isOwn ? "flex-end" : "flex-start";
    
                    return `
                    <div style="display:flex; align-items:flex-start; justify-content:${align}; margin-bottom:10px;">
                        ${!isOwn ? `
                        <div style="width:32px; height:32px; background:#ccc; border-radius:50%; display:flex; align-items:center; justify-content:center; font-weight:bold; color:white; margin-right:8px;">
                            ${initials}
                        </div>` : ""}
                        <div style="background:${bubbleColor}; color:${textColor}; padding:10px 12px; border-radius:10px; max-width:75%; word-wrap:break-word;">
                            <p style="margin:0 0 4px 0;">${note.note_text}</p>
                            <small style="font-size:12px; color:${isOwn ? "rgba(255,255,255,0.8)" : "#555"};">${note.username} • ${note.created_at}</small>
                        </div>
                        ${isOwn ? `
                        <div style="width:32px; height:32px; background:#0078d4; border-radius:50%; display:flex; align-items:center; justify-content:center; font-weight:bold; color:white; margin-left:8px;">
                            ${initials}
                        </div>` : ""}
                    </div>`;
                }).join('');
    
                notesDiv.scrollTop = notesDiv.scrollHeight;
            }
        })
        .catch(() => {
            if (!silent) notesDiv.innerHTML = "<p style='color:red;'>Error loading notes.</p>";
        });
    }
    
    // When opening a log, start polling for notes
    detailLinks.forEach(link => {
        link.addEventListener('click', (e) => {
            e.preventDefault();
            activeRow = link.closest('tr');
            activeLogId = link.dataset.id;
    
            document.getElementById('modalId').textContent = activeLogId;
            document.getElementById('modalUser').textContent = link.dataset.user;
            document.getElementById('modalAction').textContent = link.dataset.action;
            document.getElementById('modalDetails').value = link.dataset.details;
            document.getElementById('modalDate').textContent = link.dataset.date;
    
            saveMsg.textContent = "";
            modal.style.display = 'block';
    
            // Load notes initially
            loadNotes(activeLogId);
    
            // Stop old polling if any
            if (notePolling) clearInterval(notePolling);
    
            // Start polling every 5 seconds
            notePolling = setInterval(() => {
                if (activeLogId) loadNotes(activeLogId, true);
            }, 5000);
        });
    });
    
    // Stop polling when modal is closed
    closeBtn.onclick = () => {
        modal.style.display = 'none';
        clearInterval(notePolling);
        activeLogId = null;
    };
    window.onclick = (e) => { 
        if (e.target === modal) { 
            modal.style.display = 'none'; 
            clearInterval(notePolling); 
            activeLogId = null;
        } 
    };
    
    // ➕ Add note
    document.getElementById('addNoteBtn').addEventListener('click', () => {
        const noteText = document.getElementById('newNote').value.trim();
        const logId = document.getElementById('modalId').textContent;
        const noteMsg = document.getElementById('noteMsg');
    
        if (noteText === "") {
            noteMsg.style.color = "red";
            noteMsg.textContent = "⚠️ Note cannot be empty.";
            return;
        }
    
        fetch('add_note.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: new URLSearchParams({ log_id: logId, note: noteText })
        })
        .then(res => res.text())
        .then(response => {
            if (response.includes("success")) {
                noteMsg.style.color = "green";
                noteMsg.textContent = "✅ Note added!";
                document.getElementById('newNote').value = "";
                loadNotes(logId);
            } else {
                noteMsg.style.color = "red";
                noteMsg.textContent = "❌ Failed to add note.";
            }
        })
        .catch(() => {
            noteMsg.style.color = "red";
            noteMsg.textContent = "⚠️ Error adding note.";
        });
    });
    </script>
</body>
</html>