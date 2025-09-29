<?php 
session_start();
if (!isset($_SESSION['user_id'])) { 
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Sports Team Manager - Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f7f7f7;
            margin: 0;
            padding: 0;
        }
        header { 
            background: #2c3e50;
            color: white; 
            padding: 15px;
            text-align: center;
        }
        .container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            padding: 30px;
        }
        .card { 
            background: white; 
            border-radius: 10px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.15);
            padding: 20px;
            text-align: center;
            transition: transform 0.2s;
        }
        .card:hover { 
            transform: translateY(-5px);
        }
        .card a {
            text-decoration: none;
            color: #2c3e50;
            font-weight: bold;
            display: block;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <header>
        <h1>⚽ Sports Team Manager</h1>
        <p>Manage Teams, Playersm Games, Stats, and Reports</p>
        <p>Welcome, <?= $_SESSION['username'] ?> | <a href="logout.php" style="color:yellow;">Logout</a></p>
    </header>

    <div class="container">
        <!-- Teams -->
         <div class="card">
            <h2>Teams</h2>
            <a href="add_team.php">➕ Add Team</a>
            <a href="manage_teams.php">📋 Manage Teams</a>
         </div>

        <!-- Games -->
         <div class="card">
            <h2>Games</h2>
            <a href="add_game.php">➕ Add Game</a>
            <a href="manage_games.php">📋 Manage Games</a>
        </div>

         <!-- Stats -->
        <div class="card">
            <h2>Stats</h2>
            <a href="add_stats.php">➕ Add Stats</a>
        </div>

        <!-- Reports -->
        <div class="card">
            <h2>Reports</h2>
            <a href="player_report.php">📊 Player Report</a>
            <a href="team_report.php">📈 Team Report</a>
        </div>
    </div>
</body>
</html>