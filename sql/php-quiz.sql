CREATE TABLE `users` (
  `user_id` INT NOT NULL AUTO_INCREMENT,
  `user_name` varchar(100) NOT NULL DEFAULT '',
  `auth_key` varchar(45) NOT NULL,
  `user_role` int(10) UNSIGNED DEFAULT NULL,
  `first_name` VARCHAR(255),
  `last_name` VARCHAR(255),
  `email` VARCHAR(255),
  PRIMARY KEY(`user_id`)
);

CREATE TABLE `quiz` (
  `quiz_id` INT NOT NULL AUTO_INCREMENT,
  `quiz_text` varchar(100) NOT NULL DEFAULT '',
  `quiz_desc` TEXT NOT NULL,
  `passing_score` int(11) NOT NULL,
  `status` int(11) NOT NULL,
  `quiz_certificate_msg` VARCHAR(255),
  PRIMARY KEY(`quiz_id`)
);

CREATE TABLE `question` (
  `question_id` INT NOT NULL AUTO_INCREMENT,
  `question_text` varchar(255) NOT NULL DEFAULT '',
  `quiz_id` INT REFERENCES quiz(quiz_id),
  `image_name` varchar(100) NOT NULL DEFAULT '',
  `status` int(11) NOT NULL,
  `order_num` int(11) NOT NULL,
  `points` int(11) NOT NULL,
  PRIMARY KEY(`question_id`)
);

CREATE TABLE `answer` (
  `answer_id` INT NOT NULL AUTO_INCREMENT,
  `answer_text` varchar(255) NOT NULL DEFAULT '',
  `correct_answer` tinyint(1) NOT NULL DEFAULT '0',
  `question_id` INT REFERENCES question(question_id),
  `order_num` int(11) NOT NULL,
  `status` int(11) NOT NULL,
  PRIMARY KEY(`answer_id`)
);

CREATE TABLE `question_results` (
  `question_results_id` INT NOT NULL AUTO_INCREMENT,
  `user_id` INT REFERENCES user(user_id),
  `question_id` INT REFERENCES question(question_id),
  `answer_id` INT REFERENCES answer(answer_id),
  `is_correct` tinyint(1) NOT NULL,
  `quiz_results_id` int(11) DEFAULT NULL,
  `create_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `question_points` int(11) NOT NULL,
  PRIMARY KEY(`question_results_id`)
);


CREATE TABLE `quiz_results` (
  `quiz_results_id` INT NOT NULL AUTO_INCREMENT,
  `quiz_id` INT REFERENCES quiz(quiz_id),
  `user_id` INT REFERENCES user(user_id),
  `status` int(11) NOT NULL,
  `complete_date` datetime NOT NULL,
  `correct_points` int(11) NOT NULL,
  `total_points` int(11) NOT NULL,
  `create_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY(`quiz_results_id`)
);




