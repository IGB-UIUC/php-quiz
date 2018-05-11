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

    private $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
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
        $question = $this->db->prepare($queryQuestion);
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
        $sql = "INSERT INTO question (question_text,quiz_id,image_name,status,order_num,points)VALUES(:question_text,:quiz_id,:image_name,:status,:order_num,:points)";
        $query = $this->db->prepare($sql);
        $query->execute(array(':question_text'=>$questionText,':quiz_id'=>$quizId,':image_name'=>$questionImage,':status'=>Question::ACTIVE,':order_num'=>$orderNum,':points'=>$questionPoints));
        $this->questionId = $this->db->lastInsertId();
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
        $sql = "UPDATE question SET status=:status WHERE question_id=:question_id";
        $query = $this->db->prepare($sql);
        $query->execute(array(':status'=>Question::DELETED,':question_id'=>$this->questionId));
        $this->questionStatus = Question::DELETED;
    }


    /**
     * Update question information in database with setters
     */
    public function UpdateQuestion()
    {
        $sql = "UPDATE question SET question_text=:question_text, image_name=:image_name, status=:status, order_num=:order_num, points=:points WHERE question_id=:question_id";
        $query = $this->db->prepare($sql);
        $query->execute(array(':question_text'=>$this->questionText,':image_name'=> $this->questionImage,
		':status'=>$this->questionStatus,':order_num'=>$this->questionOrder,
		':points'=>$this->questionPoints,':question_id'=>$this->questionId));
    }

    /**Get the next question in order
     * @return mixed
     */
    public function GetNextQuestion()
    {
        $sql = "SELECT question_id FROM question WHERE quiz_id=:quiz_id AND status=:status AND (order_num > :order_num) ORDER BY order_num ASC LIMIT 1";
        $query = $this->db->prepare($sql);
        $query->execute(array(':quiz_id'=>$this->quizId,':status'=>Question::ACTIVE,'order_num'=>$this->questionOrder));
        $result = $query->fetch(PDO::FETCH_ASSOC);
        return $result['question_id'];

    }

    /**Get Previous question in order
     * @return mixed
     */
    public function GetPreviousQuestion()
    {
        $sql = "SELECT question_id FROM question WHERE quiz_id=:quiz_id AND status=:status AND (order_num < :order_num) ORDER BY order_num DESC LIMIT 1";
        $query = $this->db->prepare($sql);
        $query->execute(array(':quiz_id'=>$this->quizId,':status'=>Question::ACTIVE,'order_num'=>$this->questionOrder));
        $result = $query->fetch(PDO::FETCH_ASSOC);
        return $result['question_id'];
    }

    /**Get a list of answers for this question
     * @return array
     */
    public function GetAnswers()
    {

        $sql = "SELECT * FROM answer WHERE question_id=:question_id AND status=:status ORDER BY order_num ASC";
        $query = $this->db->prepare($sql);
        $query->execute(array(':question_id'=>$this->questionId,':status'=>Answer::ACTIVE));
        return $query->fetchAll(PDO::FETCH_ASSOC);

    }

    /**Get the number of answers for this question
     * @return mixed
     */
    public function GetAnswersCount()
    {
        $sql = "SELECT COUNT(*) as answer_count FROM answer WHERE question_id=:question_id AND status=:status";
        $query = $this->db->prepare($sql);
        $query->execute(array(':question_id'=>$this->questionId,':status'=>Answer::ACTIVE));
        $result = $query->fetch(PDO::FETCH_ASSOC);

        return $result['answer_count'];
    }

    /**Set the correct answers
     * @param $correctAnswers
     */
    public function SetCorrectAnswers($correctAnswers)
    {
        $answer = new Answer($this->db);
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
        $sql = "SELECT order_num FROM question WHERE quiz_id=:quiz_id AND status=:status ORDER BY order_num DESC LIMIT 1";
        $query = $this->db->prepare($sql);
        $query->execute(array(':quiz_id'=>$quizId, ':status'=>Question::ACTIVE));
        $result = $query->fetch(PDO::FETCH_ASSOC);

        return $result['order_num'];
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
                $sql = "UPDATE question SET order_num=order_num-1 WHERE order_num <= :new_order_num AND order_num > :old_order_num AND quiz_id=:quiz_id";
            }
            elseif($newOrderNum < $this->questionOrder)
            {
                //Shift question stack up
                $sql = "UPDATE question SET order_num=order_num+1 WHERE order_num >= :new_order_num AND order_num < :old_order_num AND quiz_id=:quiz_id";
            }

            $query = $this->db->prepare($sql);
            $query->execute(array(':new_order_num'=>$newOrderNum, ':old_order_num'=>$this->questionOrder,':quiz_id'=>$this->quizId));
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
