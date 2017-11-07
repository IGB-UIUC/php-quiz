SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


CREATE TABLE `answer` (
  `answer_id` int(11) NOT NULL,
  `answer_text` varchar(255) NOT NULL DEFAULT '',
  `correct_answer` tinyint(1) NOT NULL DEFAULT '0',
  `question_id` int(11) NOT NULL DEFAULT '0',
  `order_num` int(11) NOT NULL,
  `status` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE `question` (
  `question_id` int(8) NOT NULL,
  `question_text` varchar(255) NOT NULL DEFAULT '',
  `quiz_id` int(100) NOT NULL DEFAULT '0',
  `image_name` varchar(100) NOT NULL DEFAULT '',
  `status` int(11) NOT NULL,
  `order_num` int(11) NOT NULL,
  `points` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE `question_results` (
  `question_results_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `question_id` int(11) NOT NULL DEFAULT '0',
  `answer_id` int(11) NOT NULL DEFAULT '0',
  `is_correct` tinyint(1) NOT NULL,
  `quiz_results_id` int(11) DEFAULT NULL,
  `create_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `question_points` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE `quiz` (
  `quiz_id` int(10) UNSIGNED NOT NULL,
  `quiz_text` varchar(100) NOT NULL DEFAULT '',
  `quiz_desc` varchar(100) NOT NULL DEFAULT '',
  `passing_score` int(11) NOT NULL,
  `status` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE `quiz_results` (
  `quiz_results_id` int(11) NOT NULL,
  `quiz_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `status` int(11) NOT NULL,
  `complete_date` datetime NOT NULL,
  `correct_points` int(11) NOT NULL,
  `total_points` int(11) NOT NULL,
  `create_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `user_name` varchar(100) NOT NULL DEFAULT '',
  `auth_key` varchar(45) NOT NULL,
  `user_role` int(10) UNSIGNED DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


ALTER TABLE `answer`
  ADD PRIMARY KEY (`answer_id`);

ALTER TABLE `question`
  ADD PRIMARY KEY (`question_id`);

ALTER TABLE `question_results`
  ADD PRIMARY KEY (`question_results_id`);

ALTER TABLE `quiz`
  ADD PRIMARY KEY (`quiz_id`);

ALTER TABLE `quiz_results`
  ADD PRIMARY KEY (`quiz_results_id`);

ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`);


