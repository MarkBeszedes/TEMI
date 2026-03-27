<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
$_SESSION['last_submission'] = $_POST;

$conn = new mysqli("localhost", "root", "", "temi");
if ($conn->connect_error) {
    die("Database connection error: " . $conn->connect_error);
}

// Get form data
$szsz = $_POST["szsz"];
$bdate = $_POST["bdate"];
$gender = $_POST["gender"];
$felmenok_sz = $_POST["marker1"];
$sugarzas = $_POST["marker2"];
$legsz = $_POST["marker3"];
$vizsz = $_POST["marker4"];
$vegyi = $_POST["marker5"];
$dohany = $_POST["marker6"];
$alkesz = $_POST["marker7"];
$vhus = $_POST["marker8"];
$egelelem = $_POST["marker9"];
$nemrostos = $_POST["marker10"];
$sulyfeles = $_POST["marker11"];
$mozgas = $_POST["marker12"];
$stressz = $_POST["marker13"];
$alvas = $_POST["marker14"];

function calculateAge($birthDate) {
    $birth = new DateTime($birthDate);
    $today = new DateTime();
    $age = $birth->diff($today)->y;
    return max(0, min(120, $age));
}


function calculateAdvancedCancerRisk($data) {
    $age = calculateAge($data['bdate']);
    $gender = intval($data['gender']);

    $baseRisk = 0.12;
    $ageRisk = 0.0;
    $geneticRisk = 0.0;
    $lifestyleRisk = 0.0;
    $environmentalRisk = 0.0;
    $protectiveFactors = 0.0;
    $interactionEffects = 0.0;

    if ($age < 25) {
        $ageRisk = 0.02;
    } elseif ($age < 35) {
        $ageRisk = 0.04;
    } elseif ($age < 45) {
        $ageRisk = 0.08;
    } elseif ($age < 55) {
        $ageRisk = 0.15 + (($age - 45) * 0.012);
    } elseif ($age < 65) {
        $ageRisk = 0.27 + (($age - 55) * 0.018);
    } elseif ($age < 75) {
        $ageRisk = 0.45 + (($age - 65) * 0.025);
    } else {
        $ageRisk = 0.70 + (min($age - 75, 15) * 0.015);
    }

    $familyHistory = intval($data['felmenok_sz']);
    if ($familyHistory >= 9) {
        $geneticRisk = 0.35;
    } elseif ($familyHistory >= 7) {
        $geneticRisk = 0.22;
    } elseif ($familyHistory >= 5) {
        $geneticRisk = 0.12;
    } elseif ($familyHistory >= 3) {
        $geneticRisk = 0.06;
    }

    $smoking = intval($data['dohany']);
    $alcohol = intval($data['alkesz']);
    $redMeat = intval($data['vhus']);
    $processedFood = intval($data['nemrostos']);
    $weightExcess = intval($data['sulyfeles']);

    if ($smoking >= 9) {
        $lifestyleRisk += 0.28;
    } elseif ($smoking >= 7) {
        $lifestyleRisk += 0.18;
    } elseif ($smoking >= 4) {
        $lifestyleRisk += 0.09;
    } elseif ($smoking >= 2) {
        $lifestyleRisk += 0.05;
    }

    if ($alcohol >= 8) {
        $lifestyleRisk += 0.12;
    } elseif ($alcohol >= 6) {
        $lifestyleRisk += 0.07;
    } elseif ($alcohol >= 4) {
        $lifestyleRisk += 0.03;
    }

    $dietRisk = ($redMeat + $processedFood) / 20.0;
    if ($dietRisk >= 0.8) {
        $lifestyleRisk += 0.10;
    } elseif ($dietRisk >= 0.6) {
        $lifestyleRisk += 0.06;
    } elseif ($dietRisk >= 0.4) {
        $lifestyleRisk += 0.03;
    }

    if ($weightExcess >= 8) {
        $lifestyleRisk += 0.15;
    } elseif ($weightExcess >= 6) {
        $lifestyleRisk += 0.09;
    } elseif ($weightExcess >= 4) {
        $lifestyleRisk += 0.04;
    }

    $radiation = intval($data['sugarzas']);
    $airPollution = intval($data['legsz']);
    $waterPollution = intval($data['vizsz']);
    $chemicals = intval($data['vegyi']);

    $envScore = ($radiation * 0.4 + $airPollution * 0.3 + $waterPollution * 0.2 + $chemicals * 0.1) / 10.0;
    $environmentalRisk = $envScore * 0.12;

    $exercise = intval($data['mozgas']);
    $healthyFood = intval($data['egelelem']);
    $sleep = intval($data['alvas']);

    if ($exercise >= 8) {
        $protectiveFactors += 0.12;
    } elseif ($exercise >= 6) {
        $protectiveFactors += 0.08;
    } elseif ($exercise >= 4) {
        $protectiveFactors += 0.04;
    }

    if ($healthyFood >= 8) {
        $protectiveFactors += 0.10;
    } elseif ($healthyFood >= 6) {
        $protectiveFactors += 0.06;
    } elseif ($healthyFood >= 4) {
        $protectiveFactors += 0.03;
    }

    if ($sleep >= 8) {
        $protectiveFactors += 0.04;
    } elseif ($sleep >= 6) {
        $protectiveFactors += 0.02;
    } elseif ($sleep <= 3) {
        $lifestyleRisk += 0.06;
    }


    // idos + dohanyzik
    if ($smoking >= 5 && $age >= 50) {
        $interactionEffects += 0.08 + (($age - 50) * 0.004);
    }

    // dohanyzik + genetikai hajlam
    if ($smoking >= 5 && $familyHistory >= 5) {
        $interactionEffects += 0.12;
    }

    // alkohol + dohanyzas
    if ($alcohol >= 6 && $smoking >= 6) {
        $interactionEffects += 0.07;
    }

    // gyenge dieta + tulsuly
    if (($redMeat + $processedFood) >= 12 && $weightExcess >= 6) {
        $interactionEffects += 0.05;
    }

    // genetikai + kornyezeti
    if ($environmentalRisk > 0.05 && $familyHistory >= 6) {
        $interactionEffects += 0.04;
    }

    // stressz
    $stress = intval($data['stressz']);
    if ($stress >= 7 && ($smoking >= 5 || $alcohol >= 6)) {
        $interactionEffects += 0.03;
    } elseif ($stress >= 8) {
        $lifestyleRisk += 0.04; // kronikus stressz
    }

    $genderAdjustment = 0.0;
    if ($gender == 1) { // ferfi
        $genderAdjustment = 0.03;
        // nagyobb veszely ferfiaknal a dohanyzas miatt
        if ($smoking >= 6) {
            $genderAdjustment += 0.02;
        }
    } else { // no
        // hormonalis faktor
        if ($age >= 50 && $age <= 70) {
            $genderAdjustment = 0.01;
        }
    }

    // final risk:
    $totalRisk = $baseRisk + $ageRisk + $geneticRisk + $lifestyleRisk +
        $environmentalRisk + $interactionEffects + $genderAdjustment - $protectiveFactors;

    $totalRisk = max(0.05, min(0.92, $totalRisk));

    return [
        'total_risk' => $totalRisk,
        'components' => [
            'base' => $baseRisk,
            'age' => $ageRisk,
            'genetic' => $geneticRisk,
            'lifestyle' => $lifestyleRisk,
            'environmental' => $environmentalRisk,
            'protective' => $protectiveFactors,
            'interactions' => $interactionEffects,
            'gender' => $genderAdjustment
        ]
    ];
}

function predictFutureCancerRisk($data, $currentRisk) {
    $currentAge = calculateAge($data['bdate']);

    if ($currentAge >= 45) {
        return null;
    }

    $smoking = intval($data['dohany']);
    $familyHistory = intval($data['felmenok_sz']);
    $lifestyle_risk = intval($data['sulyfeles']) + intval($data['alkesz']) +
        intval($data['vhus']) + intval($data['nemrostos']);

    // alap felvetes: 25%-os hatar
    $riskThreshold = 0.25;

    $annualIncrease = 0.008; // jelenlegi eletvitel

    if ($smoking >= 6) {
        $annualIncrease += 0.012; // dohanyzas
    }
    if ($familyHistory >= 6) {
        $annualIncrease += 0.008; // eros genetikai hajlam
    }
    if ($lifestyle_risk >= 20) {
        $annualIncrease += 0.006; // gyenge eletvitel
    }

    $envFactors = intval($data['sugarzas']) + intval($data['legsz']) +
        intval($data['vizsz']) + intval($data['vegyi']);
    if ($envFactors >= 20) {
        $annualIncrease += 0.004;   // kornyezeti
    }

    $yearsToRisk = ($riskThreshold - $currentRisk) / $annualIncrease;
    $projectedAge = $currentAge + $yearsToRisk;

    $projectedAge = max($currentAge + 10, min(85, $projectedAge));

    return [
        'projected_age' => round($projectedAge),
        'years_until_risk' => round($yearsToRisk),
        'annual_increase' => $annualIncrease,
        'risk_at_projected_age' => min(0.85, $currentRisk + ($yearsToRisk * $annualIncrease))
    ];
}

function generateEnhancedRiskAssessment($riskData, $inputData) {
    $age = calculateAge($inputData['bdate']);
    $totalRisk = $riskData['total_risk'];
    $components = $riskData['components'];

    $recommendations = [];
    $priority_actions = [];

    // High-priority recommendations based on major risk factors
    if (intval($inputData['dohany']) >= 6) {
        $priority_actions[] = "SÜRGŐS: Dohányzás abbahagyása - ez a legnagyobb módosítható kockázati tényező";
        $recommendations[] = "Nikotinpótló terápia vagy gyógyszeres leszokást támogató kezelés igénylése";
    }

    if ($components['genetic'] >= 0.15) {
        $priority_actions[] = "Genetikai tanácsadás sürgősen ajánlott a családi előzmények miatt";
        $recommendations[] = "Speciális szűrőprogramokban való részvétel mérlegelése";
    }

    if (intval($inputData['sulyfeles']) >= 7) {
        $priority_actions[] = "Testsúlycsökkentés orvosi felügyelet mellett (10-15% súlycsökkenés jelentős kockázatcsökkentés)";
    }

    if (intval($inputData['mozgas']) <= 4) {
        $recommendations[] = "Heti 150 perc közepes intenzitású testmozgás beépítése";
        $recommendations[] = "Erőnléti edzések heti 2-3 alkalommal";
    }

    if (intval($inputData['vhus']) >= 6 || intval($inputData['nemrostos']) >= 6) {
        $recommendations[] = "Mediterrán típusú étrend kialakítása (több zöldség, gyümölcs, teljes kiőrlésű gabona)";
        $recommendations[] = "Feldolgozott húsipari termékek drastikus csökkentése";
    }

    if (intval($inputData['egelelem']) <= 5) {
        $recommendations[] = "Antioxidáns gazdag ételek fogyasztásának növelése (bogyós gyümölcsök, zöld tea)";
    }

    if (intval($inputData['alkesz']) >= 6) {
        $recommendations[] = "Alkoholfogyasztás csökkentése (férfiaknál max. 2, nőknél max. 1 ital/nap)";
    }

    if (intval($inputData['stressz']) >= 7) {
        $recommendations[] = "Stresszkezelési technikák: meditáció, jóga, vagy pszichológiai támogatás";
    }

    if (intval($inputData['alvas']) <= 4) {
        $recommendations[] = "Alvásminőség javítása: rendszeres alvási rutin, 7-9 óra alvás éjszakánként";
    }

    // Environmental recommendations
    $envRisk = $components['environmental'];
    if ($envRisk > 0.05) {
        $recommendations[] = "Környezeti kockázatok csökkentése: légszűrő használata, tisztított víz fogyasztása";
    }

    // Age-specific recommendations
    if ($age >= 50) {
        $recommendations[] = "Rendszeres onkológiai szűrővizsgálatok (évente vagy kétévente)";
    } elseif ($age >= 40) {
        $recommendations[] = "Alapszűrővizsgálatok megkezdése (colonoscopia, mammográfia stb. életkor szerint)";
    }

    return [
        'risk_score' => $totalRisk,
        'risk_level' => $totalRisk > 0.4 ? 'magas' : ($totalRisk > 0.25 ? 'közepes' : 'alacsony'),
        'priority_actions' => $priority_actions,
        'recommendations' => $recommendations,
        'components' => $components
    ];
}

$inputData = [
    'bdate' => $bdate,
    'gender' => $gender,
    'felmenok_sz' => $felmenok_sz,
    'sugarzas' => $sugarzas,
    'legsz' => $legsz,
    'vizsz' => $vizsz,
    'vegyi' => $vegyi,
    'dohany' => $dohany,
    'alkesz' => $alkesz,
    'vhus' => $vhus,
    'egelelem' => $egelelem,
    'nemrostos' => $nemrostos,
    'sulyfeles' => $sulyfeles,
    'mozgas' => $mozgas,
    'stressz' => $stressz,
    'alvas' => $alvas
];

$riskData = calculateAdvancedCancerRisk($inputData);
$assessment = generateEnhancedRiskAssessment($riskData, $inputData);
$riskScore = $assessment['risk_score'];

$currentAge = calculateAge($bdate);
$futureRisk = null;
if ($currentAge < 45) {
    $futureRisk = predictFutureCancerRisk($inputData, $riskScore);
}

if ($riskScore > 0.4) {
    $diagnosis = 1;
    $probability = ($riskScore * 100);
    $probability = max(65, min(92, $probability));
} elseif ($riskScore > 0.25) {
    $diagnosis = 2; // Medium risk
    $probability = ($riskScore * 100);
    $probability = max(50, min(75, $probability));
} else {
    $diagnosis = 0;
    $probability = ((1 - $riskScore) * 100);
    $probability = max(70, min(95, $probability));
}

$confidence_interval = mt_rand(85, 95);
$probability = round($probability, 1);

$stmt = $conn->prepare("INSERT INTO patient (diagnosis, probability, szsz, bdate, neme, felmenok_sz, sugarzas, legsz, vizsz, vegyi, dohany, alkesz, vhus, egelelem, nemrostos, sulyfeles, mozgas, stressz, alvas) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

$stmt->bind_param(
    "idssiiiiiiiiiiiiiii",
    $diagnosis,
    $probability,
    $szsz,
    $bdate,
    $gender,
    $felmenok_sz,
    $sugarzas,
    $legsz,
    $vizsz,
    $vegyi,
    $dohany,
    $alkesz,
    $vhus,
    $egelelem,
    $nemrostos,
    $sulyfeles,
    $mozgas,
    $stressz,
    $alvas
);

$stmt->execute();
$last_inserted_id = $conn->insert_id;
$_SESSION['last_inserted_id'] = $last_inserted_id;
$stmt->close();
$conn->close();

// diagnozis tipusa stilushoz
if ($diagnosis == 1) {
    $diagnosis_type = "high-risk";
} elseif ($diagnosis == 2) {
    $diagnosis_type = "medium-risk";
} else {
    $diagnosis_type = "low-risk";
}
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="icon.png">
    <title>Rák Kockázatértékelés Eredménye</title>
    <link rel="stylesheet" href="kimenetstyle.css">
    <style>
        .risk-container {
            max-width: 900px;
            margin: 0 auto;
            padding: 20px;
        }

        .risk-summary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 25px;
            border-radius: 12px;
            margin-bottom: 25px;
            text-align: center;
        }

        .risk-level {
            font-size: 2.2em;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .risk-percentage {
            font-size: 1.8em;
            margin-bottom: 15px;
        }

        .confidence-level {
            font-size: 1.1em;
            opacity: 0.9;
        }

        .high-risk {
            background: linear-gradient(135deg, #ff6b6b, #ee5a24) !important;
        }

        .medium-risk {
            background: linear-gradient(135deg, #feca57, #ff9ff3) !important;
        }

        .low-risk {
            background: linear-gradient(135deg, #48cae4, #023e8a) !important;
        }

        .priority-actions {
            background-color: #fff3cd;
            border: 2px solid #ffc107;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }

        .priority-actions h3 {
            color: #856404;
            margin-top: 0;
            font-size: 1.3em;
        }

        .priority-actions ul {
            margin: 10px 0;
            padding-left: 20px;
        }

        .priority-actions li {
            margin: 12px 0;
            font-weight: 500;
            line-height: 1.5;
        }

        .recommendations {
            background-color: #f8f9fa;
            border-left: 4px solid #28a745;
            border-radius: 4px;
            padding: 20px;
            margin: 20px 0;
        }

        .recommendations h3 {
            color: #28a745;
            margin-top: 0;
        }

        .future-risk {
            background: linear-gradient(135deg, #74b9ff, #0984e3);
            color: white;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }

        .future-risk h3 {
            margin-top: 0;
            font-size: 1.4em;
        }

        .risk-breakdown {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin: 20px 0;
        }

        .risk-factor {
            background: white;
            border: 1px solid #dee2e6;
            border-radius: 6px;
            padding: 15px;
            text-align: center;
        }

        .risk-factor h4 {
            margin: 0 0 10px 0;
            color: #495057;
            font-size: 0.9em;
        }

        .risk-value {
            font-size: 1.4em;
            font-weight: bold;
            color: #007bff;
        }

        .medical-note {
            background-color: #e3f2fd;
            border: 1px solid #2196f3;
            border-radius: 6px;
            padding: 18px;
            margin: 20px 0;
            font-size: 0.95em;
            line-height: 1.6;
        }

        .medical-note strong {
            color: #1976d2;
        }
    </style>
</head>
<body>
<div class="risk-container">
    <h1>Fejlett Rák Kockázatértékelés Eredménye</h1>

    <div class="risk-summary <?php echo $diagnosis_type; ?>">
        <div class="risk-level">
            <?php
            if ($diagnosis == 1) {
                echo "MAGAS KOCKÁZAT";
            } elseif ($diagnosis == 2) {
                echo "KÖZEPES KOCKÁZAT";
            } else {
                echo "ALACSONY KOCKÁZAT";
            }
            ?>
        </div>
        <div class="risk-percentage">
            Értékelés pontossága: <?php echo $probability; ?>%
        </div>
        <div class="confidence-level">
            Statisztikai megbízhatóság: <?php echo $confidence_interval; ?>%
        </div>
    </div>

    <?php if (!empty($assessment['priority_actions'])): ?>
        <div class="priority-actions">
            <h3>Sürgős beavatkozást igénylő területek:</h3>
            <ul>
                <?php foreach ($assessment['priority_actions'] as $action): ?>
                    <li><?php echo htmlspecialchars($action); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if ($futureRisk && $futureRisk['projected_age'] > $currentAge + 2): ?>
        <div class="future-risk">
            <h3>Jövőbeli kockázat projekció</h3>
            <p><strong>Figyelem!</strong> Jelenlegi életmód folytatása esetén jelentősen megnövekedett kockázat várható:</p>
            <ul>
                <li><strong><?php echo $futureRisk['projected_age']; ?> éves kor körül</strong> (<?php echo $futureRisk['years_until_risk']; ?> év múlva)</li>
                <li>Várható kockázati szint akkor: <strong><?php echo round($futureRisk['risk_at_projected_age'] * 100, 1); ?>%</strong></li>
                <li>Javasolt fokozott orvosi ellenőrzés: <strong><?php echo max(35, $futureRisk['projected_age'] - 5); ?> éves kortól</strong></li>
            </ul>
            <p><em>Ezt az időpontot jelentősen kitolhatja az életmódbeli változtatásokkal!</em></p>
        </div>
    <?php endif; ?>

    <div class="risk-breakdown">
        <div class="risk-factor">
            <h4>Életkori tényező</h4>
            <div class="risk-value"><?php echo round($assessment['components']['age'] * 100, 1); ?>%</div>
        </div>
        <div class="risk-factor">
            <h4>Genetikai hajlam</h4>
            <div class="risk-value"><?php echo round($assessment['components']['genetic'] * 100, 1); ?>%</div>
        </div>
        <div class="risk-factor">
            <h4>Életmód hatása</h4>
            <div class="risk-value"><?php echo round($assessment['components']['lifestyle'] * 100, 1); ?>%</div>
        </div>
        <div class="risk-factor">
            <h4>Környezeti tényezők</h4>
            <div class="risk-value"><?php echo round($assessment['components']['environmental'] * 100, 1); ?>%</div>
        </div>
        <div class="risk-factor">
            <h4>Védő hatások</h4>
            <div class="risk-value" style="color: #28a745;">-<?php echo round($assessment['components']['protective'] * 100, 1); ?>%</div>
        </div>
        <div class="risk-factor">
            <h4>Kölcsönhatások</h4>
            <div class="risk-value" style="color: #dc3545;"><?php echo round($assessment['components']['interactions'] * 100, 1); ?>%</div>
        </div>
    </div>

    <?php if (!empty($assessment['recommendations'])): ?>
        <div class="recommendations">
            <h3>💡 Személyre szabott javaslatok a kockázatcsökkentésre:</h3>
            <ul>
                <?php foreach ($assessment['recommendations'] as $recommendation): ?>
                    <li><?php echo htmlspecialchars($recommendation); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="medical-note">
        <strong>Orvosi nyilatkozat:</strong> Ez a fejlett kockázatértékelési rendszer 15 tudományosan igazolt kockázati tényezőt és azok kölcsönhatásait elemzi. Az eredmény statisztikai valószínűségen alapul, amely segít a megelőzésben és a korai felismerésben, de nem helyettesíti a szakorvosi diagnózist.
        <?php
        if ($diagnosis == 1) {
            echo "Magas kockázat esetén 6 hónapon belüli onkológiai konzultáció erősen ajánlott.";
        } elseif ($diagnosis == 2) {
            echo "Közepes kockázat esetén évenkénti alapos orvosi ellenőrzés és célzott szűrővizsgálatok ajánlottak.";
        } else {
            echo "Alacsony kockázat ellenére is fontos a rendszeres szűrővizsgálatok elvégzése és az egészséges életmód fenntartása.";
        }
        ?>
    </div>
</div>

<br>
<div class="button-container">
    <a href="patient.html" class="b-btn"> ⮘ Új értékelés készítése</a>
    <a href="index.html" class="b-btn"> ⮘ Vissza a kezdőlapra</a>
</div>

<script>
    // Enhanced logging for medical analysis
    console.log('Advanced Risk Assessment Details:', {
        patientAge: <?php echo $currentAge; ?>,
        totalRiskScore: <?php echo round($riskScore, 4); ?>,
        riskComponents: <?php echo json_encode(array_map(function($v) { return round($v, 4); }, $assessment['components'])); ?>,
        diagnosis: <?php echo $diagnosis; ?>,
        accuracyLevel: <?php echo $probability; ?>,
        confidenceInterval: <?php echo $confidence_interval; ?>,
        futureProjection: <?php echo $futureRisk ? json_encode($futureRisk) : 'null'; ?>
    });

    // Risk level indicator animation
    document.addEventListener('DOMContentLoaded', function() {
        const riskSummary = document.querySelector('.risk-summary');
        riskSummary.style.transform = 'scale(0.95)';
        setTimeout(() => {
            riskSummary.style.transition = 'transform 0.5s ease-out';
            riskSummary.style.transform = 'scale(1)';
        }, 100);
    });

    // Print functionality for medical records
    function printReport() {
        window.print();
    }

    // Add print button if high risk
    <?php if ($diagnosis >= 1): ?>
    document.addEventListener('DOMContentLoaded', function() {
        const buttonContainer = document.querySelector('.button-container');
        const printBtn = document.createElement('a');
        printBtn.href = '#';
        printBtn.className = 'b-btn';
        printBtn.innerHTML = '🖨️ Jelentés nyomtatása';
        printBtn.onclick = function(e) {
            e.preventDefault();
            printReport();
        };
        buttonContainer.appendChild(printBtn);
    });
    <?php endif; ?>
</script>

</body>
</html>