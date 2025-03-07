<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>BMI Calculator</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <style>
        @keyframes fadeOut {
            0% { opacity: 1; }
            80% { opacity: 1; }
            100% { opacity: 0; display: none; }
        }
        .fade-out {
            animation: fadeOut 5s forwards;
        }
    </style>
</head>
<body class="bg-dark text-light">
    <div class="container mt-5">
        <h1 class="text-center text-primary mb-4">BMI Calculator</h1>

        <?php
        $db = new SQLite3('bmi_data.db');
        $db->exec('CREATE TABLE IF NOT EXISTS bmi_records (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT,
            weight REAL,
            height REAL,
            bmi REAL,
            interpretation TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )');

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['name'], $_POST['weight'], $_POST['height'])) {
            $name = htmlspecialchars(trim($_POST['name']));
            $weight = floatval($_POST['weight']);
            $height = floatval($_POST['height']) / 100;

            if ($weight <= 0 || $height <= 0 || empty($name)) {
                echo "<div class='alert alert-danger text-center fade-out'>Invalid input values.</div>";
            } else {
                $bmi = $weight / ($height * $height);
                $interpretation = match (true) {
                    $bmi < 18.5 => 'Underweight',
                    $bmi < 25 => 'Normal weight',
                    $bmi < 30 => 'Overweight',
                    default => 'Obesity',
                };

                $stmt = $db->prepare('SELECT COUNT(*) FROM bmi_records WHERE name = :name AND weight = :weight AND height = :height');
                $stmt->bindValue(':name', $name);
                $stmt->bindValue(':weight', $weight);
                $stmt->bindValue(':height', $height);
                $result = $stmt->execute();
                $count = $result->fetchArray()[0];

                if ($count == 0) {
                    $stmt = $db->prepare('INSERT INTO bmi_records (name, weight, height, bmi, interpretation) VALUES (:name, :weight, :height, :bmi, :interpretation)');
                    $stmt->bindValue(':name', $name);
                    $stmt->bindValue(':weight', $weight);
                    $stmt->bindValue(':height', $height);
                    $stmt->bindValue(':bmi', $bmi);
                    $stmt->bindValue(':interpretation', $interpretation);
                    $stmt->execute();
                    echo "<div class='alert alert-success text-center fade-out'>Hello, $name. Your BMI is " . number_format($bmi, 2) . " ($interpretation). Data saved successfully! ✅</div>";
                } else {
                    echo "<div class='alert alert-info text-center fade-out'>Hello, $name. Your data already exists in the records. ✅</div>";
                }
            }
        }
        ?>

        <form action="" method="post" id="bmiForm" class="card p-4 shadow-lg">
            <div class="mb-3">
                <label for="name" class="form-label">Name:</label>
                <input type="text" id="name" name="name" class="form-control" required />
            </div>
            <div class="mb-3">
                <label for="weight" class="form-label">Weight (kg):</label>
                <input type="number" id="weight" name="weight" class="form-control" step="any" min="0" required />
            </div>
            <div class="mb-3">
                <label for="height" class="form-label">Height (cm):</label>
                <input type="number" id="height" name="height" class="form-control" step="any" min="0" required />
            </div>
            <button id="calculate" type="submit" class="btn btn-primary w-100" disabled>Calculate</button>
        </form>

        <h2 class="text-center text-primary mt-5">BMI Records</h2>
        <ul class="list-group mt-3">
            <?php
            $results = $db->query('SELECT name, bmi, interpretation, created_at FROM bmi_records ORDER BY created_at DESC');
            while ($row = $results->fetchArray(SQLITE3_ASSOC)) {
                echo "<li class='list-group-item bg-dark text-light'>" .
                    "{$row['name']} - BMI: " . number_format($row['bmi'], 2) . 
                    " ({$row['interpretation']}) on {$row['created_at']}" . 
                    "</li>";
            }
            ?>
        </ul>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('bmiForm');
            const calculateButton = document.getElementById('calculate');
            const weightInput = document.getElementById('weight');
            const heightInput = document.getElementById('height');

            form.addEventListener('input', function() {
                const name = document.getElementById('name').value.trim();
                const weight = parseFloat(weightInput.value);
                const height = parseFloat(heightInput.value);
                calculateButton.disabled = !(name && weight > 0 && height > 0);
            });

            weightInput.addEventListener('input', function() {
                const weight = parseFloat(weightInput.value);
                if (!isNaN(weight) && weight > 100) {
                  const weightAlerts = [
                    "You don't need to calculate, you are fat.",
                    "Bro, try not to eat the monitor.",
                    "Are you sure that's your weight, not your Wi-Fi password?",
                    "The scale said 'one at a time', bro.",
                    "Your chair is working overtime right now.",
                    "If we roll you down a hill, you'll hit terminal velocity.",
                    "You're not fat — you're just harder to kidnap.",
                    "Your shadow has its own zip code.",
                    "The scale didn't give a number — it just said 'ouch'.",
                    "NASA just called — you’re messing with the gravitational field.",
                    "Bro, you're living proof that 'all you can eat' is a challenge, not a suggestion.",
                    "Even your reflection is asking for a gym membership.",
                    "You're not fat — you're just easier to see.",
                    "The gym called — they miss you.",
                    "Are you on a seafood diet? You see food and eat it.",
                    "Even your shadow takes a break halfway.",
                    "Bro, the only thing you lift is the TV remote.",
                    "When you step on the scale, it says 'To be continued…'",
                    "You're proof that gravity works… overtime.",
                    "The elevator called — it’s now stairs only.",
                    "You're the reason snacks come in 'family size'.",
                    "Even your fridge gave up and moved out.",
                    "You're so big, when you wear a yellow coat, people shout 'Taxi!'",
                    "Your belt has more notches than your Wi-Fi signal.",
                    "If you run, the ground applies for worker’s comp.",
                    "Bro, you're not fat — you're just in your 'extended storage' phase.",
                    "The scale didn't show numbers — it just said 'Help'."
                  ];
                    alert(weightAlerts[Math.floor(Math.random() * weightAlerts.length)]);
                }
            });

            heightInput.addEventListener('input', function() {
                const height = parseFloat(heightInput.value);
                if (!isNaN(height) && height < 170 && height > 50) {
                  const heightAlerts = [
                    "Need a ladder, shorty?",
                    "You're fun-sized, not short.",
                    "How's the weather down there?",
                    "You're not short — you're just concentrated awesomeness!",
                    "You should meet my cat, you're the same height.",
                    "Being small means you can hide anywhere!",
                    "Are you standing, or did you sit down already?",
                    "You're travel-sized for convenience!",
                    "Careful, a gust of wind might carry you away!",
                    "I bet you can use a puddle as a swimming pool.",
                    "You're not short — you're just closer to the Earth's core.",
                    "Life must be easy — every seat is a front-row seat!",
                    "Are you a hobbit? Is the Shire nearby?",
                    "I didn't know action figures could use this calculator!",
                    "I hear you use a step stool to reach the bottom shelf."
                  ]; 
                    alert(heightAlerts[Math.floor(Math.random() * heightAlerts.length)]);
                }
            });
        });
    </script>
</body>
</html>
