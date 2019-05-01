<?php
/**
 * Created by PhpStorm.
 * User: nevoband
 * Date: 6/20/14
 * Time: 11:19 AM
 */

class QuestionResults {

    private $db;
    private $quizResultsId;
    private $questionResultsId;
    private $userId;
    private $answerId;
    private $isCorrect;
    private $questionId;
    private $createDate;
    private $questionPoints;

    public function __construct(PDO $db)
    {
        $this->db = $db;
        //Default values
        $this->questionResultsId = 0;
        $this->questionPoints = 0;
        $this->isCorrect = 0;
    }

    public function __destruct()
    {

    }

    /**Create a new question results in database and load it into this object
     * @param $quizResultsId
     * @param $userId
     * @param $answerId
     * @param $questionId
     */
    public function CreateResults($quizResultsId,$userId,$answerId,$questionId)
    {
        $sql = "INSERT INTO question_results (quiz_results_id,user_id,answer_id,is_correct,question_id,question_points) ";
	$sql .= "VALUES(:quiz_results_id,:user_id,:answer_id,:is_correct,:question_id,:question_points)";

        $query = $this->db->prepare($sql);
        $query->execute(array(':quiz_results_id'=>$quizResultsId,':user_id'=>$userId,':answer_id'=>$answerId,
		':is_correct'=>$this->isCorrect,':question_id'=>$questionId,':question_points'=>$this->questionPoints));
        $questionResultsId = $this->db->lastInsertId();

        if($questionResultsId)
        {
            $this->questionResultsId = $questionResultsId;
            $this->quizResultsId = $quizResultsId;
            $this->userId = $userId;
            $this->answerId = $answerId;
            $this->questionId = $questionId;
        }
    }

    /**Load a question results form database into object
     * @param $questionResultsId
     */
    public function LoadResults($questionResultsId)
    {
        $sql = "SELECT * FROM question_results WHERE question_results_id=:quiz_results_id";
        $query = $this->db->prepare($sql);
        $query->execute(array('quiz_results_id'=>$questionResultsId));
        $results = $query->fetchAll(PDO::FETCH_ASSOC);

        if(count($results))
        {
            $this->questionResultsId = $results['question_results_id'];
            $this->quizResultsId = $results['quiz_results_id'];
            $this->userId = $results['user_id'];
            $this->answerId = $results['answer_id'];
            $this->isCorrect = $results['is_correct'];
            $this->questionId = $results['question_id'];
            $this->createDate = $results['create_date'];
            $this->questionPoints = $results['question_points'];
        }
    }

    /**
     * Update question from setters
     */
    public function UpdateResults()
    {
        $sql = "UPDATE question_results SET quiz_results_id=:quiz_results_id, user_id=:user_id, answer_id=:answer_id, ";
	$sql .= "is_correct=:is_correct, question_id=:question_id, question_points=:question_points ";
	$sql .= "WHERE question_results_id=:question_results_id";
        $query = $this->db->prepare($sql);
        $query->execute(array(':quiz_results_id'=>$this->quizResultsId,':user_id'=>$this->userId,':answer_id'=>$this->answerId,
		':is_correct'=>$this->isCorrect,':question_id'=>$this->questionId,':question_points'=>$this->questionPoints,
		':question_results_id'=>$this->questionResultsId));

    }

    /**Delete results
     * @param $userId
     * @param $quizResultsId
     */
    public function DeleteResults($userId,$quizResultsId)
    {
        $sql = "DELETE * FROM results WHERE user_id=:user_id AND quiz_results_id=:quiz_results_id";
        $query = $this->db->prepare($sql);
        $query->execute(array(':user_id'=>$userId,':quiz_results_id'=>$quizResultsId));
    }

    //Getters and Setters

    /**
     * @param mixed $answerId
     */
    public function setAnswerId($answerId)
    {
        $this->answerId = $answerId;
    }

    /**
     * @return mixed
     */
    public function getAnswerId()
    {
        return $this->answerId;
    }

    /**
     * @param mixed $isCorrect
     */
    public function setIsCorrect($isCorrect)
    {
        $this->isCorrect = $isCorrect;
    }

    /**
     * @return mixed
     */
    public function getIsCorrect()
    {
        return $this->isCorrect;
    }

    /**
     * @return mixed
     */
    public function getQuestionId()
    {
        return $this->questionId;
    }

    /**
     * @param mixed $questionPoints
     */
    public function setQuestionPoints($questionPoints)
    {
        $this->questionPoints = $questionPoints;
    }

    /**
     * @return mixed
     */
    public function getQuestionPoints()
    {
        return $this->questionPoints;
    }

    /**
     * @return mixed
     */
    public function getQuizResultsId()
    {
        return $this->quizResultsId;
    }

    /**
     * @return mixed
     */
    public function getQuestionResultsId()
    {
        return $this->questionResultsId;
    }

    /**
     * @param mixed $userId
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;
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
    public function getCreateDate()
    {
        return $this->createDate;
    }
}
