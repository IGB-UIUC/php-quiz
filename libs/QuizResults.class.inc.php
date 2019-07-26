<?php
/**
 * Created by PhpStorm.
 * User: nevoband
 * Date: 7/15/14
 * Time: 12:55 PM
 */

class QuizResults {

    const PASSED=1,IN_PROGRESS=0,FAILED=3;
    const date_format = "F j, Y";
    private $db;
    private $quizResultsId;
    private $quizId;
    private $userId;
    private $status;
    private $completeDate;
    private $createDate;
    private $correctPoints;
    private $totalPoints;
    

    public function __construct(PDO $db)
    {
        $this->db = $db;
        $this->quizResultsId = 0;

    }

    public function __destruct()
    {

    }

    /**Create a new quiz results in the database and load it into this object
     * @param $userId
     * @param $quizId
     */
    public function CreateQuizResults($userId,$quizId)
    {
        $sql = "INSERT INTO quiz_results (quiz_id,user_id,status,correct_points, total_points)VALUES(:quiz_id,:user_id,:status,:correct_points,:total_points)";
        $query = $this->db->prepare($sql);
        $query->execute(array(':quiz_id'=>$quizId,':user_id'=>$userId,':status'=>QuizResults::IN_PROGRESS,':correct_points'=>0,':total_points'=>0));
        $quizResultsId = $this->db->LastInsertId();

        if($quizResultsId)
        {
            $this->quizResultsId = $quizResultsId;
            $this->quizId = $quizId;
            $this->userId = $userId;
            $this->status = QuizResults::IN_PROGRESS;
            $this->correctPoints = 0;
            $this->totalPoints = 0;
            $this->completeDate = null;
            $this->createDate = null;
        }
    }

    /**Load quiz results from database into this object
     * @param $quizResultsId
     */
    public function LoadQuizResults($quizResultsId)
    {
        $sql = "SELECT * FROM quiz_results WHERE quiz_results_id=:quiz_results_id LIMIT 1";
        $query = $this->db->prepare($sql);
        $query->execute(array(':quiz_results_id'=>$quizResultsId));
        $result = $query->fetch(PDO::FETCH_ASSOC);
	if ($result) {
            $this->quizResultsId = $result['quiz_results_id'];
            $this->quizId = $result['quiz_id'];
            $this->userId = $result['user_id'];
            $this->status = $result['status'];
            $this->completeDate = $result['complete_date'];
            $this->correctPoints = $result['correct_points'];
            $this->totalPoints = $result['total_points'];
            $this->createDate = $result['create_date'];
	}
    }

    /**
     * Update quiz results from setters
     */
    public function UpdateQuizResults()
    {
        $sql = "UPDATE quiz_results SET quiz_id=:quiz_id, user_id=:user_id,status=:status,";
	$sql .= "correct_points=:correct_points,total_points=:total_points,complete_date=:complete_date ";
	$sql .= "WHERE quiz_results_id=:quiz_results_id";
        $query = $this->db->prepare($sql);
        $query->execute(array(':quiz_id'=>$this->quizId,
		':user_id'=>$this->userId,
		':status'=>$this->status,
		':correct_points'=>$this->correctPoints,
		':total_points'=>$this->totalPoints,
		':quiz_results_id'=>$this->quizResultsId,
		':complete_date'=>$this->completeDate
		));
    }

    /**Grade this quiz
     * If update scores is set to true -> then all points and correct answers will be updated from
     * the questions to questions results.
     * If update scores is set to false -> only use the points form questions results don't update them from the questions table
     * This is only useful when you want to import from the old version of the program
     * @param bool $updateScores
     */
    public function GradeQuiz($updateScores=true)
    {
        $this->CalculateQuizScore($updateScores);

        $quiz = new Quiz($this->db);
        $quiz->LoadQuiz($this->quizId);

        if( ($this->correctPoints / $this->totalPoints) * 100 >= $quiz->getQuizPassScore())
        {
            $this->status = QuizResults::PASSED;
        }
        else
        {
            $this->status = QuizResults::FAILED;
        }

        $sql = "UPDATE quiz_results SET complete_date=NOW(), total_points=:total_points, correct_points=:correct_points,status=:status ";
	$sql .= "WHERE quiz_results_id=:quiz_results_id";
        $query = $this->db->prepare($sql);
	$query->execute(array(':quiz_results_id'=>$this->quizResultsId,':total_points'=>$this->totalPoints,':correct_points'=>$this->correctPoints,':status'=>$this->status));
    }

    /**Check if the quiz was passed by the user
     * @param $userId
     * @param $quizId
     * @return bool
     */
    public function isPassed($userId,$quizId)
    {
        $sql = "SELECT status FROM quiz_results WHERE user_id=:user_id AND quiz_id=:quiz_id AND status=:status";
        $query = $this->db->prepare($sql);
        $query->execute(array(':user_id'=>$userId,':quiz_id'=>$quizId,':status'=>QuizResults::PASSED));
        $result = $query->fetchAll(PDO::FETCH_ASSOC);

        if(count($result))
        {
            return true;
        }

        return false;
    }

    /**Get the current quiz results of id of the give quiz in progress
     * @param $userId
     * @param $quizId
     * @return int
     */
    public function QuizInProgress($userId,$quizId)
    {
        $sql = "SELECT quiz_results_id FROM quiz_results ";
	$sql .= "WHERE user_id=:user_id AND quiz_id=:quiz_id AND status!=:status ";
	$sql .= "ORDER BY status DESC";
        $query = $this->db->prepare($sql);
        $query->execute(array(':user_id'=>$userId,':quiz_id'=>$quizId,':status'=>QuizResults::FAILED));
        $result = $query->fetch(PDO::FETCH_ASSOC);

        if($result)
        {
            return $result['quiz_results_id'];
        }

        return 0;
    }

    /**List results for given quiz and user id
     * @param $userId
     * @param $quizId
     * @return mixed
     */
    public function QuizResultsList($userId,$quizId)
    {
        $sql = "SELECT quiz_results_id,status, correct_points, total_points ";
	$sql .= "FROM quiz_results WHERE user_id=:user_id AND quiz_id=:quiz_id";
        $query = $this->db->prepare($sql);
        $query->execute(array(':user_id'=>$userId,':quiz_id'=>$quizId));
        return $query->fetch(PDO::FETCH_ASSOC);

    }

    /**List results for all users for a given quiz id
     * @param $quizId
     * @return array
     */
    public function UsersQuizResultsList($quizId)
    {
	$sql = "SELECT quiz_results.quiz_results_id, quiz_results.status, quiz_results.correct_points, ";
	$sql .= "quiz_results.total_points, quiz_results.complete_date, quiz_results.user_id, ";
	$sql .= "users.user_name ";
	$sql .= "FROM quiz_results LEFT JOIN users ON users.user_id=quiz_results.user_id ";
	$sql .= "WHERE quiz_id=:quiz_id ";
	$sql .= "ORDER BY users.user_name ASC ";
        $query = $this->db->prepare($sql);
        $query->execute(array(':quiz_id'=>$quizId));
        return $query->fetchAll(PDO::FETCH_ASSOC);

    }

    /**List questions and their results for this quiz results
     * @return array
     */
    public function QuestionResultsList()
    {
        $sql = "SELECT q.question_id, qr.user_id, qr.quiz_results_id, q.quiz_id, qr.answer_id, qr.is_correct, qr.question_points, q.order_num, qr.question_results_id ";
	$sql .= "FROM (SELECT * FROM question WHERE quiz_id=:quiz_id AND status=:status ORDER by order_num) as q ";
	$sql .= "LEFT JOIN (SELECT * FROM question_results WHERE quiz_results_id=:quiz_results_id) as qr ON qr.question_id = q.question_id ";
	$sql .= "ORDER BY order_num ASC";
        $query = $this->db->prepare($sql);
        $query->execute(array(':quiz_results_id'=>$this->quizResultsId,':quiz_id'=>$this->quizId,':status'=>Question::ACTIVE));
        return $query->fetchAll(PDO::FETCH_ASSOC);

    }

    /**Set the question results for this quiz results
     * either creates or modifies a question results object which corresponds to this quiz results
     * @param $questionId
     * @param $answerId
     */
    public function SetQuestionResults($questionId,$answerId)
    {
        $questionResults = $this->GetQuestionResults($questionId);
        if($questionResults->getQuestionResultsId())
        {
            $questionResults->setAnswerId($answerId);
            $questionResults->UpdateResults();
        }
        else
        {
            $questionResults->CreateResults($this->quizResultsId, $this->userId, $answerId, $questionId);
        }
    }

    /**Get the question results for the given question id in this quiz results
     * @param $questionId
     * @return QuestionResults
     */
    public function GetQuestionResults($questionId)
    {
        $questionResults = new QuestionResults($this->db);

        $sql = "SELECT question_results_id FROM question_results ";
	$sql .= "WHERE quiz_results_id=:quiz_results_id AND question_id=:question_id";
        $query = $this->db->prepare($sql);
        $query->execute(array(':quiz_results_id'=>$this->quizResultsId,':question_id'=>$questionId));
        $result = $query->fetch(PDO::FETCH_ASSOC);

        if($result)
        {
            $questionResults->LoadResults($result['question_results_id']);
        }

        return $questionResults;
    }

    /**Calculate the quiz socre
     * if update scores is set to true -> then question points,correct answers etc.. will be updated from the question table
     * otherwise the current values in question results will be used.
     * @param bool $updateScores
     */
    private function CalculateQuizScore($updateScores=true)
    {
        if($updateScores)
        {
		//Update question results pointsi to the latest ones
		$sql = "UPDATE question_results qr, question q, answer a SET qr.question_points = q.points, qr.is_correct = a.correct_answer ";
		$sql .= "WHERE qr.question_id=q.question_id AND qr.quiz_results_id=:quiz_results_id AND a.answer_id=qr.answer_id";
		$query = $this->db->prepare($sql);
		$query->execute(array(':quiz_results_id'=>$this->quizResultsId));
        }

        //Calculate points from question results points
        $queryQuizScore = "SELECT SUM(question_points) as total_points, SUM(CASE WHEN is_correct=1 THEN question_points ELSE 0 END) as correct_points ";
	$queryQuizScore .= "FROM question_results WHERE user_id=:user_id AND quiz_results_id=:quiz_results_id";
        $quizScore = $this->db->prepare($queryQuizScore);
        $quizScore->execute(array(':user_id'=>$this->userId,':quiz_results_id'=>$this->quizResultsId));
        $result = $quizScore->fetch(PDO::FETCH_ASSOC);

        $this->correctPoints = $result['correct_points'];
        $this->totalPoints = $result['total_points'];
    }

    /**
     * Delete this quiz results
     */
    public function Delete()
    {
        $sql = "DELETE FROM question_results WHERE quiz_results_id=:quiz_results_id";
        $query = $this->db->prepare($sql);
        $query->execute(array(':quiz_results_id'=>$this->quizResultsId));

        $sql = "Delete FROM quiz_results WHERE quiz_results_id=:quiz_results_id";
        $query = $this->db->prepare($sql);
        $query->execute(array(':quiz_results_id'=>$this->quizResultsId));
    }
    /**
     * @param mixed $correctPoints
     */
    public function setCorrectPoints($correctPoints)
    {
        $this->correctPoints = $correctPoints;
    }

    /**
     * @return mixed
     */
    public function getCorrectPoints()
    {
        return $this->correctPoints;
    }

    /**
     * @return mixed
     */
    public function getQuizId()
    {
        return $this->quizId;
    }

    /**
     * @return mixed
     */
    public function getQuizResultsId()
    {
        return $this->quizResultsId;
    }

    /**
     * @param mixed $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    public function getStatusText($statusId)
    {
        switch($statusId)
        {
            case QuizResults::PASSED:
                return 'Passed';
                break;
            case QuizResults::FAILED:
                return 'Failed';
                break;
            case QuizResults::IN_PROGRESS:
                return 'Not Complete';
                break;
            default:
                return 'n/a';
        }
    }

    /**
     * @param mixed $totalPoints
     */
    public function setTotalPoints($totalPoints)
    {
        $this->totalPoints = $totalPoints;
    }

    /**
     * @return mixed
     */
    public function getTotalPoints()
    {
        return $this->totalPoints;
    }


    /**
     * @return mixed
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * @return mixed
     */
    public function getCompleteDate()
    {
        return $this->completeDate;
    }

    public function getFormatedCompleteDate() {
	return date(QuizResults::date_format,strtotime($this->getCompleteDate()));

    }
    /**
     * @param mixed $completeDate
     */
    public function setCompleteDate($completeDate)
    {
        $this->completeDate = date('Y-m-d H:i:s',strtotime($completeDate));
    }

    /**
     * @return mixed
     */
    public function getCreateDate()
    {
        return $this->createDate;
    }

    /**
     * @param mixed $createDate
     */
    public function setCreateDate($createDate)
    {
        $this->createDate = date('Y-m-d H:i:s',strtotime($createDate));
    }
} 
