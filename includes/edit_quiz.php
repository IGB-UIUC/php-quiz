<?php

$questionStatusArr = array(Question::ACTIVE=>"Active",Question::DELETED=>"Deleted");

$quiz = new Quiz($sqlDataBase);
if(isset($_GET['quiz_id']))
{
    $quiz->LoadQuiz($_GET['quiz_id']);
}

if(isset($_POST['update_quiz']))
{
    $quiz->setQuizName($_POST['quiz_name']);
    $quiz->setQuizDescription($_POST['quiz_desc']);
    $quiz->setQuizPassScore($_POST['pass_score']);
    $quiz->UpdateQuiz();
}

if(isset($_POST['add_question']))
{
    $question = new Question($sqlDataBase);
    $question->CreateQuestion($quiz->getQuizId(),$_POST['question_text'],'',DEFAULT_QUESTION_POINTS);
}

//Only used when importing questions in order to generate initial order numbers
if(isset($_POST['auto_order']))
{
    $questionOrder = 1;
    $question = new Question($sqlDataBase);
    $answer = new Answer($sqlDataBase);
    $questionsList = $quiz->ListQuestions();

    foreach($questionsList as $id => $questionInfo)
    {
        $question->LoadQuestion($questionInfo['question_id']);
        $question->setQuestionOrder($questionOrder);
        $question->UpdateQuestion();
        $answersList = $question->GetAnswers();
        $answerOrder = 1;
        foreach($answersList as $id=>$answerInfo)
        {
            $answer->LoadAnswer($answerInfo['answer_id']);
            $answer->setOrderNum($answerOrder);
            $answer->UpdateAnswer();
            $answerOrder++;
        }
        $questionOrder++;
    }
}

//Delete a question or activate a question give the question option the user clicked
if(isset($_GET['action']) && isset($_GET['question_id']))
{
    $questionToModify= new Question($sqlDataBase);
    $questionToModify->LoadQuestion($_GET['question_id']);
    //Delte question
    if($_GET['action']=="del")
    {
        $questionToModify->setQuestionStatus(Question::DELETED);

    }
    //Reactivate the question after it was deleted
    if($_GET['action']=="act")
    {
        $questionToModify->setQuestionStatus(Question::ACTIVE);
    }
    $questionToModify->UpdateQuestion();
}
echo "<div class=\"panel panel-primary\">";
echo "<div class=\"panel-heading\"><h3>Edit Quiz:</h3></div>";
echo "<div class=\"panel-body\">";
echo "<a href=\"index.php?p=quizzes\"><< Back</a><br><br>";
echo "<form method=\"post\" enctype=\"multipart/form-data\" action=\"index.php?p=edit_quiz&quiz_id=".$quiz->getQuizId()."\">";
echo "<h3>Quiz Name:</h3>";
echo "<textarea name=\"quiz_name\" rows=\"3\" cols=\"50\">".$quiz->getQuizName()."</textarea><br><br>";
echo "<h3>Quiz Description:</h3>";
echo "<textarea name=\"quiz_desc\" rows=\"5\" cols=\"50\">".$quiz->getQuizDescription()."</textarea><br>";
echo "Passing Score: <input type=\"text\" name=\"pass_score\" size=2 value=\"".$quiz->getQuizPassScore()."\">%<br>";
echo "<input type=\"submit\" value=\"Update Quiz\" name=\"update_quiz\" style=\"background-color:#dbeaf5;\"><br><br>";
echo "<h3>Add Question:</h3>";
echo "<textarea name=\"question_text\" rows=\"5\" cols=\"50\"></textarea><br>";
echo "<input type=\"submit\" value=\"Add Question\" name=\"add_question\" style=\"background-color:#dbeaf5;\"><br><br>";
//echo "<input type=\"submit\" value=\"Auto Order\" name=\"auto_order\" style=\"background-color:#dbeaf5;\">";
echo "</form>";

//List all questions for this quiz, list two tables one for active questions and one for deleted ones
foreach($questionStatusArr as $statusId => $statusDescription)
{
    $questionsList = $quiz->ListQuestions($statusId);
    echo "<div class=\"panel panel-info\">";
    echo "<div class=\"panel-heading\"><h3>".$statusDescription."</h3></div>";
    echo "<div class=\"panel-body\">";
    echo "<table class=\"table\">";
    foreach($questionsList as $id=>$questionInfo)
    {
        echo "<tr>
            <td><b>".$questionInfo['order_num']."</b>)  </td>
            <td>".$questionInfo['question_text']."</td>
            <td><a href=\"index.php?p=edit_question&question_id=".$questionInfo['question_id']."\">Edit</a>";

            if($statusId==Question::ACTIVE)
            {
                echo " <a href=\"index.php?p=edit_quiz&action=del&quiz_id=".$quiz->getQuizId()."&question_id=".$questionInfo['question_id']."\">Delete</a></td>";
            }
            if($statusId==Question::DELETED)
            {
                echo " <a href=\"index.php?p=edit_quiz&action=act&quiz_id=".$quiz->getQuizId()."&question_id=".$questionInfo['question_id']."\">Activate</a></td>";
            }
        echo "</tr>";
    }
    echo "</table>";
    echo "</div>";
    echo "</div>";
}
?> 		
</div>