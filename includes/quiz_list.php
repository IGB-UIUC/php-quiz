<?php
/**
 * includes/quiz_list.php
 * List available quizzes
 */
$quiz = new Quiz($sqlDataBase);
$quizResults = new QuizResults($sqlDataBase);

$quizzesList = $quiz->ListQuizzes();

echo "<div class=\"panel panel-primary\">";
echo "<div class=\"panel-heading\"><h3>Please Select A Quiz:</h3></div>";
echo "<div class=\"panel-body\">";

echo "<table class=\"table\">";
foreach($quizzesList as $id=>$quizInfo)
{
    $quizResults->QuizInProgress($authenticate->getAuthenticatedUser()->getUserId(),$quizInfo['quiz_id']);

    $isPassed = $quizResults->isPassed($authenticate->getAuthenticatedUser()->getUserId(),$quizInfo['quiz_id']);
    $quizResultsList = $quizResults->QuizResultsList($authenticate->getAuthenticatedUser()->getUserId(),$quizInfo['quiz_id']);
    echo "<tr><td width=\"400\"><b>".$quizInfo['quiz_text']."</b></td>";
    if($isPassed)
    {
        echo "<td width=\"100\">Passed</td></tr>";
    }
    else
    {
        echo "<td width=\"100\"><a href=\"index.php?p=exam&quiz_id=".$quizInfo['quiz_id']."\">Take Quiz</a></td></tr>";
    }
}
echo "</table>";

echo "</div>";
echo "</div>";
?>