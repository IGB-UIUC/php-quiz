<?php
/**
 * includes/quiz.php
 * Star the quiz
 */
$quiz = new Quiz($sqlDataBase);
$quizResults = new QuizResults($sqlDataBase);

$question = new Question($sqlDataBase);
$questionResults = new QuestionResults($sqlDataBase);

$questionsLeft = 0;
//Load Quiz
if(isset($_GET['quiz_id']))
{
    $quiz->LoadQuiz($_GET['quiz_id']);
    $quizResultsInProgressId = $quizResults->QuizInProgress($authenticate->getAuthenticatedUser()->getUserId(),$quiz->getQuizId());

    //If there already exists a quiz in progress resume it
    if($quizResultsInProgressId)
    {
        $quizResults->LoadQuizResults($quizResultsInProgressId);
    }
    else
    {
        //If no quiz is in progress for user then create a new quiz results
        $quizResults->CreateQuizResults($authenticate->getAuthenticatedUser()->getUserId(),$quiz->getQuizId());
    }
}

//Load question given requested question_id otherwise load first question
if(isset($_GET['question_id']))
{
    $question = $quiz->LoadQuestionById($_GET['question_id']);
}
else
{
    $question = $quiz->GetFirstQuestion();
}

//Submit selected question
if(isset($_POST['submit_question']))
{
    if(isset($_POST['answer_selected']))
    {
        $quizResults->SetQuestionResults($_POST['submitted_question_id'],$_POST['answer_selected']);
    }
    else{
        $quizResults->SetQuestionResults($_POST['submitted_question_id'],0);
    }
}

//Grade quiz
if(isset($_POST['submit_quiz']))
{
   $quizResults->GradeQuiz();
   if($quizResults->getStatus() == QuizResults::FAILED)
   {
        echo "<div class=\"alert alert-danger\" role=\"alert\"><b>Failed:</b> Quiz was reset, please try again.</div>";
       $quizResults->CreateQuizResults($quizResults->getUserId(),$quizResults->getQuizId());
   }
}

//Allow the user to print a certification if they passed
if($quizResults->getStatus() == QuizResults::PASSED)
{
    echo "<div class=\"panel panel-success\">";

    echo "<div class=\"panel-heading\">Congratulations!</div>";

    echo "<div class=\"panel-body\">";
    echo "<form action=\"certificate.php\" method=\"post\">";
    echo " Please print a certificate for your quiz results: <input type=\"submit\" value=\" Print Certificate \" name=\"print_certificate\"  class=\"btn btn-success\">
            <input type=\"hidden\" name=\"user_id\" value=\"".$authenticate->getAuthenticatedUser()->getUserId()."\">
            <input type=\"hidden\" name=\"key\" value=\"".$authenticate->getAuthenticatedUser()->getAuthKey()."\">
            <input type=\"hidden\" name=\"quiz_results_id\" value=\"".$quizResults->getQuizResultsId()."\">
            <br><br>";
    echo "</form>";
    echo "</div>";

    echo "</div>";
}

$answersList = $question->GetAnswers();
$questionsResultsList = $quizResults->QuestionResultsList();

//Load User's Question Results
$questionResults = $quizResults->GetQuestionResults($question->getQuestionId());

//After submitting form go to the next question
$nextQuestion = $question->GetNextQuestion();
//Show quiz name and current progress
echo "<div class=\"panel panel-primary\">";
echo "<div class=\"panel-heading\"><h3>".$quiz->getQuizName()."</h3></div>";
echo "<div class=\"panel-body\">";
echo "<div class=\"alert alert-info\"><h4>Instructions:</h4>
      Please click on the checkbox next to your answer and click on the Select Answer button.
      <br>As questions are answered, question numbers on progress bar will turn blue.
      <br>When all questions are answered a Grade Quiz button will appear, click on this button to view your results and print your certificate.</div><br>";
echo "<br><b>Progress:</b>";
echo "<div class=\"btn-toolbar\" role=\"toolbar\" style=\"margin: 0;\">";
echo "<div class=\"btn-group btn-group-sm\">";
//List questions color green for ones completed
foreach($questionsResultsList as $id=>$questionInfo)
{
    $styleClass = "";

    if($questionInfo['answer_id'])
    {
        if($quizResults->getStatus() != QuizResults::IN_PROGRESS)
        {
            if($questionInfo['is_correct']==Answer::WRONG)
            {
                $styleClass = " class=\"btn btn-danger\"";
            }
            else
            {
                $styleClass = " class=\"btn btn-success\"";
            }
        }
        else
        {
            $styleClass = " class=\"btn btn-info\"";
        }
    }
    else
    {
        $styleClass = " class=\"btn btn-default\"";
        $questionsLeft++;
    }

    //If quiz was completed then show the user which questions they got wrong


    //Print out the questions in order and create links to each with order number as value
    echo "<button type=\"button\" ".$styleClass."  onClick=\"parent.location='index.php?p=exam&quiz_id=".$question->getQuizId()."&question_id=".$questionInfo['question_id']."'\">".$questionInfo['order_num']."</button>";
}
echo "</div></div>";

//Form start
echo "<form action=\"index.php?p=exam&quiz_id=".$question->getQuizId()."&question_id=".( ($nextQuestion)?$nextQuestion:$question->getQuestionId() )."\" method=\"post\">";
//If there are not questions left to answer then show the grade quiz button
if($questionsLeft == 0 && $quizResults->getStatus()==QuizResults::IN_PROGRESS)
{
    echo "<br><br><b>All questions answered: </b>Please click here when you are ready to <input type=\"submit\" value=\" Grade Quiz \" name=\"submit_quiz\" class=\"btn btn-warning\">";
}

//Show question number
echo "<hr><br><b>Question ".$question->getQuestionOrder().":</b><br>";
if(file_exists(UPLOAD_DIR.$question->getQuestionId().".jpg"))
{
    echo "<img src=\"".UPLOAD_DIR.$question->getQuestionId().".jpg\"><br>";
}

//Show Question
echo "<p class=\"bg-info\"><b>".$question->getQuestionText()."</b></p>";

//List available answers
echo "<table class=\"table\">";

foreach($answersList as $id=>$answerInfo)
{
    echo "<tr><td  style=\"vertical-align: middle\"><input type=\"radio\" name=\"answer_selected\" value=\"".$answerInfo['answer_id']."\"";
    if($answerInfo['answer_id']==$questionResults->getAnswerId())
    {
        echo  " checked=\"checked\"";
    }
    echo ">  ".$answerInfo['answer_text']." </td></tr>";
}
echo "</table>";

//Question submitted
echo "<input type=\"hidden\" name=\"submitted_question_id\" value=\"".$question->getQuestionId()."\">";
if($quizResults->getStatus() == QuizResults::IN_PROGRESS)
{
    echo "<input type=\"submit\" value=\" Select Answer \" name=\"submit_question\" class=\"btn btn-primary\">";
}

echo "</form>";

echo "</div>";
echo "</div>"
?>
