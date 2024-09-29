<?php

    require_once("secure.php");

    if (isset($_SESSION['username'])) {
        // echo 'Session Username: ' . $_SESSION['username'];
        $MaintenanceID = $_SESSION['username'];
    }else {
        die("User is not logged in.");
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statistics</title>
    <link rel="icon" type="image/x-icon" href="pictures/resque-logo.png">
    <link rel="stylesheet" href="styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <!-- Link to the FontAwesome library for icons -->
    <script src="https://kit.fontawesome.com/ddbf4d6190.js" crossorigin="anonymous"></script>
</head>
<body>
<?php


        // include database details from config.php file
        require_once("config.php");

        // attempt to make database connection
        $connection = new mysqli(SERVERNAME, USERNAME, PASSWORD, DATABASE);

        // Check if connection was successful
        if ($connection->connect_error) {
            die("<p class=\"error\">Connection failed: Incorrect credentials or Database not available!</p>");
        }
        
        //fetch all the halls for the maintenance dashboard
        $halls = "SELECT DISTINCT hall_name FROM hall_secretary";

        $residences_result = $connection->query($halls);

        if ($residences_result === FALSE) {
            die("<p class=\"error\">Query was Unsuccessful!</p>");
        }

        $halls = "SELECT DISTINCT hall_name FROM hall_secretary";

        $residences_result = $connection->query($halls);

        if ($residences_result === FALSE) {
            die("<p class=\"error\">Query was Unsuccessful!</p>");
        }


        $sql = "SELECT concat(f_Name,  ' ', l_Name) as 'name' FROM maintenance_staff  WHERE userName = '$MaintenanceID'";
        $thename = $connection -> query($sql);


        if ($thename === FALSE) {
            die("<p class=\"error\">Query was Unsuccessful!</p>");
        }

        $name = $thename->fetch_assoc();        

    ?>
    <div class="container">
    <?php
    require_once("sidebarMaintenance.php");
    ?>
    <main class="content">
        <header class="header">
            <h2>Statistics</h2>
        </header>
        <nav class="houses">
                <?php
                    
                    $active = 0;
                    while ($residence = $residences_result->fetch_assoc()) {
                        
                        if ($active == 0) {
                            $active++;
                            $defaulthouse = $residence['hall_name'];
                        }

                        $activeHouse = isset($_REQUEST['house_name']) ? $_REQUEST['house_name'] : $defaulthouse;
                        $isActive = ($residence['hall_name'] === $activeHouse) ? 'active' : '';
                        echo "<a href='Stats_maintenance.php?house_name={$residence['hall_name']}' class='house-link {$isActive}'>{$residence['hall_name']}</a>";
                    }
                ?>
        </nav>
                
        <div class="stats-overview">
            <?php 

                
                $ticket_status = array("Requisitioned", "Closed", "Resolved", " ");
                $icons = array("pictures/layer.svg", "pictures/clipboard-tick.svg", "pictures/task.svg");
                $class_names = array("card-icon", "card-icon1", "card-icon2");
                $names = array("Requisitioned Tickets", "Closed Tickets", "Resolved Tickets");
                $ticketTotals = array('Electrical','Plumbing','Furniture', 'Heater', 'Other');
                $index = 0;
                $total = 0;
                
                echo '<div class = "cards">';
               //for each loop for rendering the Pending, Processing, Closed and Total tickets
                foreach($ticket_status as $status){
                    if(isset($_REQUEST['house_name'])){
                        $resname = $_REQUEST['house_name'];
                        $sql = "SELECT * FROM ticket join residence on ticket.resName = residence.resName where hall_name = '$resname' AND ticket_status = '$status' ";
                    }
                    else{
                        $sql = "SELECT * FROM ticket join residence on ticket.resName = residence.resName where hall_name = '$defaulthouse' AND ticket_status = '$status' ";
                    }

                    $result = $connection->query($sql);

                    // Check if query successfull
                    if ($result === FALSE) {
                        die("<p class=\"error\">Query was Unsuccessful this one!</p>");
                    }
                    
                    if($index < 3){
                        echo "<div class=\"card\" >";

                        echo      "<div class= {$class_names[$index]}>";
                        echo           "<img src={$icons[$index]} alt = 'Icon' >";
                        echo      "</div>";

                        echo      "<div class= \"card-info\" >";

                        echo          "<div class= \"card-number\">";
                        echo             $result -> num_rows;
                        echo           "</div>";

                        echo           "<div class=\"card-text\">";
                        echo             $names[$index];
                        echo           "</div>";
                        
                        echo       "</div>";

                        echo "</div>";
                    }

                    else{
                        echo "<div class=\"card\" >";

                        echo      "<div class= 'card-icon3' >";
                        echo           "<img src='pictures/clipboard-text.svg' alt = 'Icon' >";
                        echo      "</div>";

                        echo      "<div class= \"card-info\" >";

                        echo          "<div class= \"card-number\">";
                        echo              $total;
                        echo           "</div>";

                        echo           "<div class=\"card-text\">";
                        echo             'Total Tickets';
                        echo           "</div>";
                        
                        echo       "</div>";

                        echo "</div>";
                    }
                    
                    //$ticketTotals[$index] = $result -> num_rows;
                    $index++;
                    $total += $result -> num_rows;
                }

                echo '</div>';

             
            ?>


        <div class="chartlayout">
            <div class="charts" id="line-chart">
                <canvas id="ticketsChart"></canvas>
            </div>
            <div class="charts" id="pie-chart">
                <canvas id="ticketStatusChart"></canvas>
            </div>
        </div>

        
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

        <script>

            //code for the line chart
            const ctx = document.getElementById('ticketsChart').getContext('2d');

            const myLineChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep'],
                    datasets: [{
                        label: 'Tickets',
                        data: [
                            <?php
                                $num = 1;
                                while($num <= 9){
                                    if(isset($_REQUEST['house_name'])){
                                        $resname = $_REQUEST['house_name'];
                                        $sql = "SELECT * FROM ticket join residence on ticket.resName = residence.resName where hall_name = '$resname' AND MONTH(ticketDate) = '$num' ";
                                    }
                                    else{
                                        $sql = "SELECT * FROM ticket join residence on ticket.resName = residence.resName where hall_name = '$defaulthouse' AND MONTH(ticketDate) = '$num' ";
                                    }
                                    $result = $connection -> query($sql);

                                    // Check if query successfull
                                    if ($result === FALSE) {
                                        die("<p class=\"error\">Query was Unsuccessful!</p>");
                                    }

                                    echo ($result -> num_rows).",";

                                    $num++;

                                }
                            ?>
                            ],
                        fill: true,
                        borderColor: '#7e5bef',
                        backgroundColor: 'rgba(126, 91, 239, 0.1)',
                        pointBackgroundColor: '#fff',
                        pointBorderColor: '#7e5bef',
                        pointHoverBackgroundColor: '#7e5bef',
                        pointHoverBorderColor: '#fff',
                        pointRadius: 6,
                        pointHoverRadius: 8,
                        lineTension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 50,

                        },

                        x: {
                            grid:{
                                display: false
                            }
                        }
                        
                    },
                    plugins: {

                        legend: {
                            display: false  
                        },

                        title: {
                            display: true,  
                            text: 'Total Number of Tickets per Month',  
                            align: 'start',  
                            font: {
                            size: 18,  
                            weight: 'bold'  
                            },
                            padding: {
                                top: 10,
                                bottom: 30
                            }
                        },

                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return `Tickets: ${context.raw}`;
                                }
                            }
                        }
                    }
                }
            });


            //code for the piechart
            const thechart = document.getElementById('ticketStatusChart').getContext('2d');
            const ticketStatusChart = new Chart(thechart, {
                type: 'doughnut', // Use 'doughnut' for the circular chart
                data: {
                    labels: ['Electrical', 'Plumbing', 'Furniture','Heater', 'Other' ],
                    datasets: [{
                        data: [
                            <?php 
                                foreach($ticketTotals as $total){
                                    if(isset($_REQUEST['house_name'])){
                                        $sql = "SELECT * FROM ticket join residence on ticket.resName = residence.resName where category = '$total' AND hall_name = '$resname' ";
                                    }
                                    else{
                                        $sql = "SELECT * FROM ticket join residence on ticket.resName = residence.resName where category = '$total' AND hall_name = '$defaulthouse' ";
                                    }
                                    $result = $connection -> query($sql);
                                    echo ($result -> num_rows).",";

                                }

                                
                                // close connection
                                $connection->close();
                            ?>
                        ], // Data points
                        backgroundColor: ['#444444', '#A2D9CE', '#85C1E9', '#FF6F61', '#FFD700'], // Colors for each section
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'right', // Position legend to the right
                            labels: {
                                usePointStyle: true, // Use dots as legend markers
                                boxWidth: 10,
                                padding: 20,
                                color: '#000'
                            }
                        },
                        title: {
                            display: true,  
                            text: 'Distribution of Current Tickets',  
                            align: 'start',  
                            font: {
                            size: 18,  
                            weight: 'bold'  
                            },
                            padding: {
                                top: 10,
                                bottom: 30
                            }
                        }
                    }
                }
            });
            
            //javascript for the bargraph
            var thebar = document.getElementById('myBarChart').getContext('2d');
            var myBarChart = new Chart(thebar, {
                type: 'bar',
                data: {
                    labels: ['January', 'February', 'March', 'April', 'May', 'June', 'July'],
                    datasets: [{
                        label: 'Sales',
                        data: [65, 59, 80, 81, 56, 55, 40],
                        backgroundColor: [
                            'rgba(255, 99, 132, 0.2)',
                            'rgba(54, 162, 235, 0.2)',
                            'rgba(255, 206, 86, 0.2)',
                            'rgba(75, 192, 192, 0.2)',
                            'rgba(153, 102, 255, 0.2)',
                            'rgba(255, 159, 64, 0.2)',
                            'rgba(75, 192, 192, 0.2)'
                        ],
                        borderColor: [
                            'rgba(255, 99, 132, 1)',
                            'rgba(54, 162, 235, 1)',
                            'rgba(255, 206, 86, 1)',
                            'rgba(75, 192, 192, 1)',
                            'rgba(153, 102, 255, 1)',
                            'rgba(255, 159, 64, 1)',
                            'rgba(75, 192, 192, 1)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });

          </script>
          <script src="script.js"></script>
        </main>
    </div>

</body>
</html>