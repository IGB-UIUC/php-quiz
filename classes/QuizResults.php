<?php
/**
 * Created by PhpStorm.
 * User: nevoband
 * Date: 7/15/14
 * Time: 12:55 PM
 */

class QuizResults {

    const PASSED=1,IN_PROGRESS=0,FAILED=3;

    private $sqlDataBase;
    private $quizResultsId;
    private $quizId;
    private $userId;
    private $status;
    private $completeDate;
    private $createDate;
    private $correctPoints;
    private $totalPoints;

    public function __construct(PDO $sqlDataBase)
    {
        $this->sqlDataBase = $sqlDataBase;
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
        $queryInsertQuizResults = "INSERT INTO quiz_results (quiz_id,user_id,status,correct_points, total_points,create_date)VALUES(:quiz_id,:user_id,:status,:correct_points,:total_points,NOW())";
        $insertQuizResults = $this->sqlDataBase->prepare($queryInsertQuizResults);
        $insertQuizResults->execute(array(':quiz_id'=>$quizId,':user_id'=>$userId,':status'=>QuizResults::IN_PROGRESS,':correct_points'=>0,':total_points'=>0));
        $quizResultsId = $this->sqlDataBase->LastInsertId();

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
        $querySelectQuizResults = "SELECT * FROM quiz_results WHERE quiz_results_id=:quiz_results_id";
        $selectQuizResults = $this->sqlDataBase->prepare($querySelectQuizResults);
        $selectQuizResults->execute(array(':quiz_results_id'=>$quizResultsId));
        $selectQuizResultsArr = $selectQuizResults->fetch(PDO::FETCH_ASSOC);
        if($selectQuizResultsArr)
        {
            $this->quizResultsId = $selectQuizResultsArr['quiz_results_id'];
            $this->quizId = $selectQuizResultsArr['quiz_id'];
            $this->userId = $selectQuizResultsArr['user_id'];
            $this->status = $selectQuizResultsArr['status'];
            $this->completeDate = $selectQuizResultsArr['create_date'];
            $this->correctPoints = $selectQuizResultsArr['correct_points'];
            $this->totalPoints = $selectQuizResultsArr['total_points'];
            $this->createDate = $selectQuizResultsArr['create_date'];
        }
    }

    /**
     * Update quiz results from setters
     */
    public function UpdateQuizResults()
    {
        $queryUpdateQuizResults = "UPDATE quiz_results SET quiz_id=:quiz_id, user_id=:user_id,status=:status,correct_points=:correct_points,total_points=:total_points,complete_date=:complete_date  WHERE quiz_results_id=:quiz_results_id";
        $updateQuizResults = $this->sqlDataBase->prepare($queryUpdateQuizResults);
        $updateQuizResults->execute(array(':quiz_id'=>$this->quizId,':user_id'=>$this->userId,':status'=>$this->status,':correct_points'=>$this->correctPoints,':total_points'=>$this->totalPoints,':quiz_results_id'=>$this->quizResultsId,':complete_date'=>$this->completeDate));
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

        $quiz = new Quiz($this->sqlDataBase);
        $quiz->LoadQuiz($this->quizId);

        if( ($this->correctPoints / $this->totalPoints) * 100 >= $quiz->getQuizPassScore())
        {
            $this->status = QuizResults::PASSED;
        }
        else
        {
            $this->status = QuizResults::FAILED;
        }

        $queryUpdateCompleteDate = "UPDATE quiz_results SET complete_date=NOW(), total_points=:total_points, correct_points=:correct_points,status=:status WHERE quiz_results_id=:quiz_results_id";
        $updateCompleteDate = $this->sqlDataBase->prepare($queryUpdateCompleteDate);
        $updateCompleteDate->execute(array(':quiz_results_id'=>$this->quizResultsId,':total_points'=>$this->totalPoints,':correct_points'=>$this->correctPoints,':status'=>$this->status));
    }

    /**Check if the quiz was passed by the user
     * @param $userId
     * @param $quizId
     * @return bool
     */
    public function isPassed($userId,$quizId)
    {
        $queryQuizPassed = "SELECT status FROM quiz_Results WHERE user_id=:user_id AND quiz_id=:quiz_id AND status=:status";
        $quizPassed = $this->sqlDataBase->prepare($queryQuizPassed);
        $quizPassed->execute(array(':user_id'=>$userId,':quiz_id'=>$quizId,':status'=>QuizResults::PASSED));
        $quizPassedArr = $quizPassed->fetch(PDO::FETCH_ASSOC);

        if($quizPassedArr)
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
        $queryQuizInProgress = "SELECT quiz_results_id FROM quiz_results WHERE user_id=:user_id AND quiz_id=:quiz_id AND status!=:status ORDER BY status DESC";
        $quizInProgress = $this->sqlDataBase->prepare($queryQuizInProgress);
        $quizInProgress->execute(array(':user_id'=>$userId,':quiz_id'=>$quizId,':status'=>QuizResults::FAILED));
        $quizInProgressArr = $quizInProgress->fetch(PDO::FETCH_ASSOC);

        if($quizInProgressArr)
        {
            return $quizInProgressArr['quiz_results_id'];
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
        $queryQuizResultsList = "SELECT quiz_results_id,status, correct_points, total_points FROM quiz_results WHERE user_id=:user_id AND quiz_id=:quiz_id";
        $quizResultsList = $this->sqlDataBase->prepare($queryQuizResultsList);
        $quizResultsList->execute(array(':user_id'=>$userId,':quiz_id'=>$quizId));
        $quizResultsListArr = $quizResultsList->fetch(PDO::FETCH_ASSOC);

        return $quizResultsListArr;
    }

    /**List results for all users for a given quiz id
     * @param $quizId
     * @return array
     */
    public function UsersQuizResultsList($quizId)
    {
        $queryQuizResultsList = "SELECT qr.quiz_results_id,qr.status, qr.correct_points, qr.total_points, qr.complete_date, u.user_name, u.user_id FROM users u LEFT JOIN quiz_results qr ON u.user_id=qr.user_id WHERE qr.quiz_id=:quiz_id OR qr.quiz_id IS NULL ORDER BY u.user_name";
        $quizResultsList = $this->sqlDataBase->prepare($queryQuizResultsList);
        $quizResultsList->execute(array(':quiz_id'=>$quizId));
        $quizResultsListArr = $quizResultsList->fetchAll(PDO::FETCH_ASSOC);

        return $quizResultsListArr;
    }

    /**List questions and their results for this quiz results
     * @return array
     */
    public function QuestionResultsList()
    {
        $queryQuizResults = "SELECT q.question_id, qr.user_id, qr.quiz_results_id, q.quiz_id, qr.answer_id, qr.is_correct, qr.question_points, q.order_num, qr.question_results_id
                            FROM
                            (SELECT * FROM question WHERE quiz_id=:quiz_id AND status=:status ORDER by order_num) as q
                            LEFT JOIN
                            (SELECT * FROM question_results WHERE quiz_results_id=:quiz_results_id) as qr ON qr.question_id = q.question_id";
        $quizResults = $this->sqlDataBase->prepare($queryQuizResults);
        $quizResults->execute(array(':quiz_results_id'=>$this->quizResultsId,':quiz_id'=>$this->quizId,':status'=>Question::ACTIVE));
        $quizResultsArr = $quizResults->fetchAll(PDO::FETCH_ASSOC);

        return $quizResultsArr;
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
        $questionResults = new QuestionResults($this->sqlDataBase);

        $queryQuestionResultsId = "SELECT question_results_id FROM question_results WHERE quiz_results_id=:quiz_results_id AND question_id=:question_id";
        $questionResultsId = $this->sqlDataBase->prepare($queryQuestionResultsId);
        $questionResultsId->execute(array(':quiz_results_id'=>$this->quizResultsId,':question_id'=>$questionId));
        $questionResultsIdArr = $questionResultsId->fetch(PDO::FETCH_ASSOC);

        if($questionResultsIdArr)
        {
            $questionResults->LoadResults($questionResultsIdArr['question_results_id']);
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
            //Update question results points to the latest ones
            $querySetQuestionPoints = "UPDATE question_results qr, question q, answer a SET qr.question_points = q.points, qr.is_correct = a.correct_answer WHERE qr.question_id=q.question_id AND qr.quiz_results_id=:quiz_results_id AND a.answer_id=qr.answer_id";
            $setQuestionPoints = $this->sqlDataBase->prepare($querySetQuestionPoints);
            $setQuestionPoints->execute(array(':quiz_results_id'=>$this->quizResultsId));
        }

        //Calculate points from question results points
        $queryQuizScore = "SELECT SUM(question_points) as total_points, SUM(CASE WHEN is_correct=1 THEN question_points ELSE 0 END) as correct_points FROM question_results WHERE user_id=:user_id AND quiz_results_id=:quiz_results_id";
        $quizScore = $this->sqlDataBase->prepare($queryQuizScore);
        $quizScore->execute(array(':user_id'=>$this->userId,':quiz_results_id'=>$this->quizResultsId));
        $quizScoreArr = $quizScore->fetch(PDO::FETCH_ASSOC);

        $this->correctPoints = $quizScoreArr['correct_points'];
        $this->totalPoints = $quizScoreArr['total_points'];
    }

    /**
     * Delete this quiz results
     */
    public function Delete()
    {
        $queryDeleteQuestionResults = "DELETE FROM question_results WHERE quiz_results_id=:quiz_results_id";
        $deleteQuestionResults = $this->sqlDataBase->preare($queryDeleteQuestionResults);
        $deleteQuestionResults->execute(array(':quiz_results_id'=>$this->quizResultsId));

        $queryDeleteQuizResults = "Delete FROM quiz_results WHERE quiz_results_id=:quiz_results_id";
        $deleteQuizResults = $this->sqlDataBase->prepare($queryDeleteQuizResults);
        $deleteQuizResults->execute(array(':quiz_results_id'=>$this->quizResultsId));
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

    /**
     * @param mixed $completeDate
     */
    public function setCompleteDate($completeDate)
    {
        $this->completeDate = $this->createDate = date('Y-m-d H:i:s',strtotime($completeDate));
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