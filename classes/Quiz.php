<?php
/**
 * Created by PhpStorm.
 * User: nevoband
 * Date: 6/20/14
 * Time: 10:17 AM
 */


class Quiz {

    const ACTIVE=0, DELETED=1;

    private $quizId;
    private $quizName;
    private $quizDescription;
    private $quizStatus;
    private $quizPassScore;

    private $sqlDataBase;


    public function __construct(PDO $sqlDataBase)
    {
        $this->sqlDataBase = $sqlDataBase;
    }

    public function __destruct()
    {

    }

    /**Load quiz from database into this object
     * @param $quizId
     */
    public function LoadQuiz($quizId)
    {
        $queryLoadQuiz = "SELECT * FROM quiz WHERE quiz_id=:quiz_id";
        $loadQuiz = $this->sqlDataBase->prepare($queryLoadQuiz);
        $loadQuiz->execute(array(":quiz_id"=>$quizId));
        $loadQuizInfo = $loadQuiz->fetch(PDO::FETCH_ASSOC);
        $this->quizName = $loadQuizInfo['quiz_text'];
        $this->quizDescription = $loadQuizInfo['quiz_desc'];
        $this->quizId = $loadQuizInfo['quiz_id'];
        $this->quizPassScore = $loadQuizInfo['passing_score'];

    }

    /**Create a new quiz in database and load it into this object
     * @param $quizName
     * @param $quizDescription
     */
    public function CreateQuiz($quizName, $quizDescription)
    {
        echo $quizName." ".$quizDescription;
        $queryInsertQuiz = "INSERT INTO quiz (quiz_text, quiz_desc,status,passing_score) VALUES(:quiz_text, :quiz_desc,:status,:passing_score)";
        echo $quizName." ".$quizDescription." ".Quiz::ACTIVE." ".DEFAULT_PASS_SCORE;
        $insertQuiz = $this->sqlDataBase->prepare($queryInsertQuiz);
        $insertQuiz->execute(array(':quiz_text'=>$quizName, ':quiz_desc'=>$quizDescription,':status'=>Quiz::ACTIVE,':passing_score'=>DEFAULT_PASS_SCORE));
        $this->quizId = $this->sqlDataBase->lastInsertId();
        $this->quizName = $quizName;
        $this->quizDescription = $quizDescription;
    }

    /**
     * Update quiz from setters
     */
    public function UpdateQuiz()
    {
        $queryUpdateQuiz = "UPDATE quiz SET quiz_text=:quiz_text, quiz_desc=:quiz_desc, status=:status, passing_score=:passing_score WHERE quiz_id=:quiz_id";
        $updateQuiz = $this->sqlDataBase->prepare($queryUpdateQuiz);
        $updateQuiz->execute(array(':quiz_text'=>$this->quizName,':quiz_desc'=>$this->quizDescription,':status'=>$this->quizStatus, ':quiz_id'=>$this->quizId,':passing_score'=>$this->quizPassScore));
    }

    /**Load question give question order number
     * @param $questionNum
     * @return Question
     */
    public function LoadQuestion($questionNum)
    {
        $question = new Question($this->sqlDataBase);
        $question->LoadQuestion($questionNum);

        return $question;
    }

    /**Load question given question id
     * @param $questionId
     * @return Question
     */
    public function LoadQuestionById($questionId)
    {
        $question = new Question($this->sqlDataBase);
        $question->LoadQuestion($questionId);

        return $question;
    }

    /**List quizzes given the status
     * @param int $status
     * @return array
     */
    public function ListQuizzes($status = Quiz::ACTIVE)
    {
        $queryQuizzes = "SELECT * FROM quiz WHERE status=:status";
        $quizzes = $this->sqlDataBase->prepare($queryQuizzes);
        $quizzes->execute(array(':status'=>$status));
        $quizzesArr = $quizzes->fetchAll(PDO::FETCH_ASSOC);

        return $quizzesArr;
    }

    /**List all quizzes
     * @return array
     */
    public function ListAllQuizzes()
    {
        $queryQuizzes = "SELECT * FROM quiz";
        $quizzes = $this->sqlDataBase->prepare($queryQuizzes);
        $quizzes->execute();
        $quizzesArr = $quizzes->fetchAll(PDO::FETCH_ASSOC);

        return $quizzesArr;
    }

    /**get the first question of the quiz
     * @return Question
     */
    public function GetFirstQuestion()
    {
        $firstQuestion = new Question($this->sqlDataBase);
        $queryQuestions = "SELECT question_id FROM question WHERE quiz_id=:quiz_id AND status=:status ORDER BY order_num ASC LIMIT 1";
        $questions = $this->sqlDataBase->prepare($queryQuestions);
        $questions->execute(array(':quiz_id'=>$this->quizId,':status'=>Question::ACTIVE));
        $questionsArr = $questions->fetch(PDO::FETCH_ASSOC);
        $firstQuestion->LoadQuestion($questionsArr['question_id']);

        return $firstQuestion;
    }

    /**List quiz questions
     * @param int $status
     * @return array
     */
    public function ListQuestions($status=Question::ACTIVE)
    {
        $queryQuestions = "SELECT * FROM question WHERE quiz_id=:quiz_id AND status=:status ORDER BY order_num ASC";
        $questions = $this->sqlDataBase->prepare($queryQuestions);
        $questions->execute(array(':quiz_id'=>$this->quizId,':status'=>$status));
        $questionsArr = $questions->fetchAll(PDO::FETCH_ASSOC);

        return $questionsArr;
    }

    /**Get the number of questions in the quiz
     * @return mixed
     */
    public function QuestionCount()
    {
        $queryQuestions = "SELECT count(*) as num_questions FROM question WHERE quiz_id=:quiz_id AND status=:status";
        $questions = $this->sqlDataBase->prepare($queryQuestions);
        $questions->execute(array(':quiz_id'=>$this->quizId,':status'=>Question::ACTIVE));
        $questionsArr = $questions->fetch(PDO::FETCH_ASSOC);

        return $questionsArr['num_questions'];
    }

    //Getters and Setters
    /**
     * @param mixed $quizName
     */
    public function setQuizName($quizName)
    {
        $this->quizName = $quizName;
    }

    /**
     * @return mixed
     */
    public function getQuizName()
    {
        return $this->quizName;
    }

    /**
     * @param mixed $quizId
     */
    public function setQuizId($quizId)
    {
        $this->quizId = $quizId;
    }

    /**
     * @return mixed
     */
    public function getQuizId()
    {
        return $this->quizId;
    }

    /**
     * @param mixed $quizDescription
     */
    public function setQuizDescription($quizDescription)
    {
        $this->quizDescription = $quizDescription;
    }

    /**
     * @return mixed
     */
    public function getQuizDescription()
    {
        return $this->quizDescription;
    }

    /**
     * @param mixed $quizStatus
     */
    public function setQuizStatus($quizStatus)
    {
        $this->quizStatus = $quizStatus;
    }

    /**
     * @return mixed
     */
    public function getQuizStatus()
    {
        return $this->quizStatus;
    }

    /**
     * @param mixed $quizPassScore
     */
    public function setQuizPassScore($quizPassScore)
    {
        $this->quizPassScore = $quizPassScore;
    }

    /**
     * @return mixed
     */
    public function getQuizPassScore()
    {
        return $this->quizPassScore;
    }



}