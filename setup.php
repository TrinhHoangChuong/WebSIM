<?php
// Setup script for Sim Thăng Long Clone
require_once 'config/database.php';

echo "<h1>Sim Thăng Long Clone - Setup</h1>";

try {
    $database = new Database();
    $db = $database->getConnection();
    
    if ($db) {
        echo "<p style='color: green;'>✅ Database connection successful!</p>";
        
        // Test API endpoints
        echo "<h2>Testing API Endpoints</h2>";
        
        // Test SIMs endpoint
        $sims_query = "SELECT COUNT(*) as count FROM sims";
        $stmt = $db->prepare($sims_query);
        $stmt->execute();
        $sims_count = $stmt->fetch()['count'];
        echo "<p>📱 Total SIMs in database: <strong>$sims_count</strong></p>";
        
        // Test Networks
        $networks_query = "SELECT * FROM networks";
        $stmt = $db->prepare($networks_query);
        $stmt->execute();
        $networks = $stmt->fetchAll();
        echo "<p>🌐 Available networks: <strong>" . count($networks) . "</strong></p>";
        
        // Test Sample SIMs
        $sample_query = "SELECT * FROM sims LIMIT 5";
        $stmt = $db->prepare($sample_query);
        $stmt->execute();
        $sample_sims = $stmt->fetchAll();
        
        echo "<h3>Sample SIMs:</h3>";
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>Phone Number</th><th>Network</th><th>Price</th><th>Type</th></tr>";
        foreach ($sample_sims as $sim) {
            echo "<tr>";
            echo "<td>" . $sim['id'] . "</td>";
            echo "<td>" . $sim['phone_number'] . "</td>";
            echo "<td>" . $sim['network'] . "</td>";
            echo "<td>" . number_format($sim['price']) . "₫</td>";
            echo "<td>" . $sim['sim_type'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // Test API URLs
        echo "<h2>API Test URLs</h2>";
        $base_url = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']);
        
        echo "<ul>";
        echo "<li><a href='$base_url/api/sims' target='_blank'>GET /api/sims</a></li>";
        echo "<li><a href='$base_url/api/sims?network=Mobifone' target='_blank'>GET /api/sims?network=Mobifone</a></li>";
        echo "<li><a href='$base_url/api/sims?min_price=1000000&max_price=3000000' target='_blank'>GET /api/sims?min_price=1000000&max_price=3000000</a></li>";
        echo "<li><a href='$base_url/api/search?q=090' target='_blank'>GET /api/search?q=090</a></li>";
        echo "<li><a href='$base_url/api/networks' target='_blank'>GET /api/networks</a></li>";
        echo "<li><a href='$base_url/api/sim-types' target='_blank'>GET /api/sim-types</a></li>";
        echo "</ul>";
        
        // Frontend URLs
        echo "<h2>Frontend URLs</h2>";
        echo "<ul>";
        echo "<li><a href='public/index.html' target='_blank'>🏠 Homepage</a></li>";
        echo "<li><a href='public/search.html' target='_blank'>🔍 Search Page</a></li>";
        echo "</ul>";
        
        echo "<h2>✅ Setup Complete!</h2>";
        echo "<p>Your Sim Thăng Long Clone is ready to use.</p>";
        echo "<p><strong>Next steps:</strong></p>";
        echo "<ol>";
        echo "<li>Visit the <a href='public/index.html'>homepage</a> to see the website</li>";
        echo "<li>Test the search functionality</li>";
        echo "<li>Try filtering by price, network, or SIM type</li>";
        echo "<li>Test the order functionality</li>";
        echo "</ol>";
        
    } else {
        echo "<p style='color: red;'>❌ Database connection failed!</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
    echo "<p>Please check your database configuration in config/database.php</p>";
}
?>

<style>
body {
    font-family: Arial, sans-serif;
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
    background: #f8f9fa;
}

h1 {
    color: #00A651;
    text-align: center;
}

h2 {
    color: #00803E;
    border-bottom: 2px solid #00A651;
    padding-bottom: 5px;
}

table {
    margin: 20px 0;
}

th, td {
    padding: 10px;
    text-align: left;
}

th {
    background: #00A651;
    color: white;
}

tr:nth-child(even) {
    background: #f2f2f2;
}

ul {
    background: white;
    padding: 20px;
    border-radius: 5px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}

li {
    margin: 10px 0;
}

a {
    color: #00A651;
    text-decoration: none;
}

a:hover {
    text-decoration: underline;
}
</style>
