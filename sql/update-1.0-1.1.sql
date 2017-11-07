ALTER TABLE question_results CHANGE is_correct is_correct BOOLEAN;
ALTER TABLE question_results CHANGE create_date create_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE quiz_results CHANGE create_date create_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE answer CHANGE correct_answer correct_answer BOOLEAN;

