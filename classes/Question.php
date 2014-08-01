<?php
/**
 * Created by PhpStorm.
 * User: nevoband
 * Date: 6/20/14
 * Time: 11:08 AM
 */


class Question {

    const DELETED=1, ACTIVE=0;
    private $questionId;
    private $quizId;
    private $questionText;
    private $questionImage;
    private $questionStatus;
    private $questionOrder;


    private $questionPoints;

    private $sqlDataBase;

    public function __construct(PDO $sqlDataBase)
    {
        $this->sqlDataBase = $sqlDataBase;
    }

    public function __destruct()
    {

    }

    /**Load question from database into this object
     * @param $questionId
     */
    public function LoadQuestion($questionId)
    {
        $queryQuestion = "SELECT * FROM question WHERE question_id=:question_id";
        $question = $this->sqlDataBase->prepare($queryQuestion);
        $question->execute(array(':question_id'=>$questionId));
        $questionInfo = $question->fetch(PDO::FETCH_ASSOC);
        $this->questionId = $questionId;
        $this->quizId = $questionInfo['quiz_id'];
        $this->questionText = $questionInfo['question_text'];
        $this->questionImage= $questionInfo['image_name'];
        $this->questionStatus = $questionInfo['status'];
        $this->questionOrder = $questionInfo['order_num'];
        $this->questionPoints= $questionInfo['points'];
    }

    /**Create a new question in database and load it into this object
     * @param $quizId
     * @param $questionText
     * @param $questionImage
     * @param $questionPoints
     */
    public function CreateQuestion($quizId,$questionText,$questionImage,$questionPoints)
    {
        $orderNum = $this->GetLastQuestionNum($quizId)+1;
        $queryInsertQuestion = "INSERT INTO question (question_text,quiz_id,image_name,status,order_num,points)VALUES(:question_text,:quiz_id,:image_name,:status,:order_num,:points)";
        $insertQuestion = $this->sqlDataBase->prepare($queryInsertQuestion);
        $insertQuestion->execute(array(':question_text'=>$questionText,':quiz_id'=>$quizId,':image_name'=>$questionImage,':status'=>Question::ACTIVE,':order_num'=>$orderNum,':points'=>$questionPoints));
        $this->questionId = $this->sqlDataBase->lastInsertId();
        $this->quizId = $quizId;
        $this->questionText = $questionText;
        $this->questionStatus = Question::ACTIVE;
        $this->questionOrder = $orderNum;
        $this->questionPoints = $questionPoints;
    }

    /**
     * Delete this question
     */
    public function DeleteQuestion()
    {
        //Just hide it
        $queryDeleteQuestion = "UPDATE question SET status=:status WHERE question_id=:question_id";
        $deleteQuestion = $this->sqlDataBase->prepare($queryDeleteQuestion);
        $deleteQuestion->execute(array(':status'=>Question::DELETED,':question_id'=>$this->questionId));
        $this->questionStatus = Question::DELETED;
    }


    /**
     * Update question information in database with setters
     */
    public function UpdateQuestion()
    {
        $queryUpdateQuestion = "UPDATE question SET question_text=:question_text, image_name=:image_name, status=:status, order_num=:order_num, points=:points WHERE question_id=:question_id";
        $updateQuestion = $this->sqlDataBase->prepare($queryUpdateQuestion);
        $updateQuestion->execute(array(':question_text'=>$this->questionText,':image_name'=> $this->questionImage,':status'=>$this->questionStatus,':order_num'=>$this->questionOrder,':points'=>$this->questionPoints,':question_id'=>$this->questionId));
    }

    /**Get the next question in order
     * @return mixed
     */
    public function GetNextQuestion()
    {
        $queryNextQuestion = "SELECT question_id FROM question WHERE quiz_id=:quiz_id AND status=:status AND (order_num > :order_num) ORDER BY order_num ASC LIMIT 1";
        $nextQuestion = $this->sqlDataBase->prepare($queryNextQuestion);
        $nextQuestion->execute(array(':quiz_id'=>$this->quizId,':status'=>Question::ACTIVE,'order_num'=>$this->questionOrder));
        $nextQuestionArr = $nextQuestion->fetch(PDO::FETCH_ASSOC);
        return $nextQuestionArr['question_id'];

    }

    /**Get Previous question in order
     * @return mixed
     */
    public function GetPreviousQuestion()
    {
        $queryPreviousQuestion = "SELECT question_id FROM question WHERE quiz_id=:quiz_id AND status=:status AND (order_num < :order_num) ORDER BY order_num DESC LIMIT 1";
        $previousQuestion = $this->sqlDataBase->prepare($queryPreviousQuestion);
        $previousQuestion->execute(array(':quiz_id'=>$this->quizId,':status'=>Question::ACTIVE,'order_num'=>$this->questionOrder));
        $previousQuestionArr = $previousQuestion->fetch(PDO::FETCH_ASSOC);
        return $previousQuestionArr['question_id'];
    }

    /**Get a list of answers for this question
     * @return array
     */
    public function GetAnswers()
    {

        $queryAnswers = "SELECT * FROM answer WHERE question_id=:question_id AND status=:status ORDER BY order_num ASC";
        $answer = $this->sqlDataBase->prepare($queryAnswers);
        $answer->execute(array(':question_id'=>$this->questionId,':status'=>Answer::ACTIVE));
        $answerArr = $answer->fetchAll(PDO::FETCH_ASSOC);

        return $answerArr;
    }

    /**Get the number of answers for this question
     * @return mixed
     */
    public function GetAnswersCount()
    {
        $queryAnswers = "SELECT COUNT(*) as answer_count FROM answer WHERE question_id=:question_id AND status=:status";
        $answer = $this->sqlDataBase->prepare($queryAnswers);
        $answer->execute(array(':question_id'=>$this->questionId,':status'=>Answer::ACTIVE));
        $answerArr = $answer->fetch(PDO::FETCH_ASSOC);

        return $answerArr['answer_count'];
    }

    /**Set the correct answers
     * @param $correctAnswers
     */
    public function SetCorrectAnswers($correctAnswers)
    {
        $answer = new Answer($this->sqlDataBase);
        $answersList = $this->GetAnswers();

        foreach($answersList as $id => $answerInfo)
        {
            if(in_array($answerInfo['answer_id'], $correctAnswers))
            {
                $answer->LoadAnswer($answerInfo['answer_id']);
                $answer->setIsCorrect(Answer::CORRECT);
                $answer->UpdateAnswer();
            }
            else
            {
                $answer->LoadAnswer($answerInfo['answer_id']);
                $answer->setIsCorrect(Answer::WRONG);
                $answer->UpdateAnswer();
            }
        }
    }

    /**Get the order number
     * @param $quizId
     * @return mixed
     */
    private function GetLastQuestionNum($quizId)
    {
        $queryLastOrderNum = "SELECT order_num FROM question WHERE quiz_id=:quiz_id AND status=:status ORDER BY order_num DESC LIMIT 1";
        $lastOrderNum = $this->sqlDataBase->prepare($queryLastOrderNum);
        $lastOrderNum->execute(array(':quiz_id'=>$quizId, ':status'=>Question::ACTIVE));
        $lastOrderNumArr = $lastOrderNum->fetch(PDO::FETCH_ASSOC);

        return $lastOrderNumArr['order_num'];
    }

    /**Updates the order of the question
     * shifts all of the questions up or down depending on where the question was inserted
     * @param $newOrderNum
     */
    public function QuestionOrder($newOrderNum)
    {
        if( ($newOrderNum != $this->questionOrder && $newOrderNum <= $this->GetLastQuestionNum($this->quizId)) || ($this->questionOrder==0) )
        {
            if($newOrderNum > $this->questionOrder)
            {
                //Shift question stack down
                $queryShiftStack = "UPDATE question SET order_num=order_num-1 WHERE order_num <= :new_order_num AND order_num > :old_order_num AND quiz_id=:quiz_id";
            }
            elseif($newOrderNum < $this->questionOrder)
            {
                //Shift question stack up
                $queryShiftStack = "UPDATE question SET order_num=order_num+1 WHERE order_num >= :new_order_num AND order_num < :old_order_num AND quiz_id=:quiz_id";
            }

            $shiftDownStack = $this->sqlDataBase->prepare($queryShiftStack);
            $shiftDownStack->execute(array(':new_order_num'=>$newOrderNum, ':old_order_num'=>$this->questionOrder,':quiz_id'=>$this->quizId));
            $this->questionOrder = $newOrderNum;
            $this->UpdateQuestion();
        }
    }

    /**
     * @return mixed
     */
    public function getQuizId()
    {
        return $this->quizId;
    }

    /**
     * @return mixeds
     */
    public function getQuestionId()
    {
        return $this->questionId;
    }

    /**
     * @param mixed $questionImage
     */
    public function setQuestionImage($questionImage)
    {
        $this->questionImage = $questionImage;
    }

    /**
     * @return mixed
     */
    public function getQuestionImage()
    {
        return $this->questionImage;
    }

    /**
     * @param mixed $questionStatus
     */
    public function setQuestionStatus($questionStatus)
    {
        $this->questionStatus = $questionStatus;
    }

    /**
     * @return mixed
     */
    public function getQuestionStatus()
    {
        return $this->questionStatus;
    }

    /**
     * @param mixed $questionText
     */
    public function setQuestionText($questionText)
    {
        $this->questionText = $questionText;
    }

    /**
     * @return mixed
     */
    public function getQuestionText()
    {
        return $this->questionText;
    }

    /**
     * @param mixed $questionOrder
     */
    public function setQuestionOrder($questionOrder)
    {
        $this->questionOrder = $questionOrder;
    }

    /**
     * @return mixed
     */
    public function getQuestionOrder()
    {
        return $this->questionOrder;
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

}
?>