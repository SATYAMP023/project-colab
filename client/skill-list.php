<div>
    <h4 class="heading">Categories by Skill</h4>
    <?php
    include('./common/db.php');
    $skillQuery = "SELECT DISTINCT skill FROM skills";
    $skillResult = $conn->query($skillQuery);
    $uniqueSkills = [];

    while ($row = $skillResult->fetch_assoc()) {
        $skillParts = explode(',', $row['skill']);
        foreach ($skillParts as $skill) {
            $skill = ucfirst(trim($skill));
            if (!in_array($skill, $uniqueSkills) && !empty($skill)) {
                $uniqueSkills[] = $skill;
            }
        }
    }
    sort($uniqueSkills);
    foreach ($uniqueSkills as $skill) {
        $encodedSkill = urlencode($skill);
        echo "<div class='row question-list' style='margin-bottom: 20px;'>
        <h4> <a href='?skill={$encodedSkill}' class='text-decoration-none text-light skill-filter' data-skill='$skill'>$skill</a> </h4>
        </div>";
    }
    ?>
</div>