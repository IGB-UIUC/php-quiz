ALTER TABLE question_results ALTER COLUMN is_correct BOOLEAN;
ALTER TABLE question_results ALTER COLUMN create_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE quiz_results ALTER COLUMN create_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE answer ALTER COLUMN correct_answer BOOLEAN;
