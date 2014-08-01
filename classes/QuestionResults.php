<?php
/**
 * Created by PhpStorm.
 * User: nevoband
 * Date: 6/20/14
 * Time: 11:19 AM
 */

class QuestionResults {

    private $sqlDataBase;
    private $quizResultsId;
    private $questionResultsId;
    private $userId;
    private $answerId;
    private $isCorrect;
    private $questionId;
    private $createDate;
    private $questionPoints;

    public function __construct(PDO $sqlDataBase)
    {
        $this->sqlDataBase = $sqlDataBase;
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
        $queryInsertResults = "INSERT INTO question_results (quiz_results_id,user_id,answer_id,is_correct,question_id,create_date,question_points)VALUES(:quiz_results_id,:user_id,:answer_id,:is_correct,:question_id,NOW(),:question_points)";
        $insertResults = $this->sqlDataBase->prepare($queryInsertResults);
        $insertResults->execute(array(':quiz_results_id'=>$quizResultsId,':user_id'=>$userId,':answer_id'=>$answerId,':is_correct'=>$this->isCorrect,':question_id'=>$questionId,':question_points'=>$this->questionPoints));
        $questionResultsId = $this->sqlDataBase->lastInsertId();

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
        $queryLoadResults = "SELECT * FROM question_results WHERE question_results_id=:quiz_results_id";
        $results = $this->sqlDataBase->prepare($queryLoadResults);
        $results->execute(array('quiz_results_id'=>$questionResultsId));
        $resultsArr = $results->fetch(PDO::FETCH_ASSOC);

        if($resultsArr)
        {
            $this->questionResultsId = $resultsArr['question_results_id'];
            $this->quizResultsId = $resultsArr['quiz_results_id'];
            $this->userId = $resultsArr['user_id'];
            $this->answerId = $resultsArr['answer_id'];
            $this->isCorrect = $resultsArr['is_correct'];
            $this->questionId = $resultsArr['question_id'];
            $this->createDate = $resultsArr['create_date'];
            $this->questionPoints = $resultsArr['question_points'];
        }
    }

    /**
     * Update question from setters
     */
    public function UpdateResults()
    {
        $queryUpdateResults = "UPDATE question_results SET quiz_results_id=:quiz_results_id, user_id=:user_id, answer_id=:answer_id, is_correct=:is_correct, question_id=:question_id, question_points=:question_points WHERE question_results_id=:question_results_id";
        $updateResults = $this->sqlDataBase->prepare($queryUpdateResults);
        $updateResults->execute(array(':quiz_results_id'=>$this->quizResultsId,':user_id'=>$this->userId,':answer_id'=>$this->answerId,':is_correct'=>$this->isCorrect,':question_id'=>$this->questionId,':question_points'=>$this->questionPoints,':question_results_id'=>$this->questionResultsId));
    }

    /**Delete results
     * @param $userId
     * @param $quizResultsId
     */
    public function DeleteResults($userId,$quizResultsId)
    {
        $queryDeleteResults = "DELETE * FROM results WHERE user_id=:user_id AND quiz_results_id=:quiz_results_id";
        $deleteResults = $this->sqlDataBase->prepare($queryDeleteResults);
        $deleteResults->execute(array(':user_id'=>$userId,':quiz_results_id'=>$quizResultsId));
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
