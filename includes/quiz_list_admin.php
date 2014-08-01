<?php 
$quiz = new Quiz($sqlDataBase);

if(isset($_POST['add_quiz']))
{
    $quiz->CreateQuiz($_POST['quiz_name'],$_POST['quiz_desc']);
}

if(isset($_GET['quiz_action']))
{
    $quiz->LoadQuiz($_GET['quiz_id']);

    if($_GET['quiz_action']=="del")
    {
        $quiz->setQuizStatus(Quiz::DELETED);
    }
    if($_GET['quiz_action']=="act")
    {
        $quiz->setQuizStatus(Quiz::ACTIVE);
    }

    $quiz->UpdateQuiz();
}
?>
<div class="panel panel-primary">
<div class="panel-heading"><h3>Quizzes List:</h3></div>
<div class="panel-body">
<form action="index.php?p=quizzes" method="post">
    <h3>Quiz Name:</h3>
    <textarea name="quiz_name" rows="3" cols="50"></textarea><br><br>
    <h3>Quiz Description:</h3>
    <textarea name="quiz_desc" rows="5" cols="50"></textarea><br><br>
    <input type="submit" value="Add Quiz" name="add_quiz" class="btn btn-primary"><br><br>
</form>
                <div class="panel panel-info">
                <div class="panel-heading"><h3>Active Quizzes:</h3></div>
                <div class="panel-body">
				<table class="table">
					<?php
                    $quizzesList = $quiz->ListQuizzes(Quiz::ACTIVE);
					foreach($quizzesList as $id=>$quizInfo)
                    {
						echo "<tr><td width=\"230\"><b>".$quizInfo['quiz_text']."</b></td>";
						echo "<td width=\"100\"><a href=\"index.php?p=edit_quiz&quiz_id=".$quizInfo['quiz_id']."\">Edit</a> | <a href=\"index.php?p=quizzes&quiz_id=".$quizInfo['quiz_id']."&quiz_action=del\">Delete</a></td>";
						echo "</tr>";
					} 
					?>
				</table>
                </div>
                </div>

                <div class="panel panel-info">
                <div class="panel-heading"><h3>Deleted Quizzes:</h3></div>
                <div class="panel-body">
                <table class="table">
                    <?php
                    $quizzesList = $quiz->ListQuizzes(Quiz::DELETED);
                    foreach($quizzesList as $id=>$quizInfo)
                    {
                        echo "<tr><td width=\"230\"><b>".$quizInfo['quiz_text']."<b></td>";
                        echo "<td width=\"100\"><a href=\"index.php?p=edit_quiz&quiz_id=".$quizInfo['quiz_id']."\">Edit</a> | <a href=\"index.php?p=quizzes&quiz_id=".$quizInfo['quiz_id']."&quiz_action=act\">Activate</a></td>";
                        echo "</tr>";
                    }
                    ?>
                </table>
                </div>
                </div>
</div>