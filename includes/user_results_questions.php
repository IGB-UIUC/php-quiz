<?php
/**
 * Created by PhpStorm.
 * User: nevoband
 * Date: 7/28/14
 * Time: 2:01 PM
 */

$question = new Question($sqlDataBase);
$answer = new Answer($sqlDataBase);
$quizResults = new QuizResults($sqlDataBase);

if(isset($_GET['quiz_results']))
{

    echo "<div class=\"panel panel-info\">";
    echo "<div class=\"panel-heading\"><h3>Quiz Results:</h3></div>";
    echo "<div class=\"panel-body\">";

    $quizResults->LoadQuizResults($_GET['quiz_results']);
    $questionResultsList = $quizResults->QuestionResultsList();

    echo "<a href=\"index.php?p=users_results&quiz_id=".$quizResults->getQuizId()."\"><< Back</a><br><br>";
    echo "<table class=\"table\">";
    echo "<th>#</th><th>Question</th><th>Points</th><th>Correct / Incorrect</th><th>User Answer</th>";
    foreach($questionResultsList as $id=>$questionResultsInfo)
    {

        $question->LoadQuestion($questionResultsInfo['question_id']);
        $answer->LoadAnswer($questionResultsInfo['answer_id']);

        echo "<tr><td>(".$question->getQuestionOrder().")</td>
                <td>".$question->getQuestionText()."</td>
                <td>".$question->getQuestionPoints()."</td>
                <td>".( ($questionResultsInfo['is_correct'])?"<div style=\"color:green\">Correct</div>":"<div style=\"color:red\">Incorrect</div>" )."</td>
                <td>".$answer->getAnswerText()."</td>
                </tr>";
    }
    echo "</table>";
    echo "</div></div>";
}


