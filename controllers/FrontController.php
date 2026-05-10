<?php

class FrontController extends Controller {

    private FrontModel $model;

    public function __construct() {
        $this->model = new FrontModel();
    }

    public function home(): void {
        $search  = trim($this->get('search', ''));
        $sortCol = $this->get('sort', 'id');
        $sortDir = $this->get('dir', 'ASC');

        $quizzes = $this->model->searchAndSort($search, $sortCol, $sortDir);

        $this->view('front/home', compact('quizzes', 'search', 'sortCol', 'sortDir'));
    }

    public function play(): void {
        $id   = (int) $this->get('id');
        $quiz = $this->model->getQuizById($id);

        if (!$quiz) { $this->view('front/not_found'); return; }

        $rows      = $this->model->getQuestionsWithShuffledAnswers($id);
        $questions = [];
        foreach ($rows as $row) {
            $qid = $row['q_id'];
            if (!isset($questions[$qid])) {
                $questions[$qid] = ['id' => $qid, 'question_text' => $row['question_text'], 'answers' => []];
            }
            $questions[$qid]['answers'][] = ['id' => $row['a_id'], 'answer_text' => $row['answer_text']];
        }

        if (empty($questions)) { $this->view('front/empty_quiz', compact('quiz')); return; }

        $this->view('front/play', compact('quiz', 'questions'));
    }

    public function submit(): void {
        if (!$this->isPost()) { $this->redirect('index.php?route=/front'); }

        $quizId      = (int) $this->post('quiz_id');
        $quiz        = $this->model->getQuizById($quizId);

        if (!$quiz) { $this->view('front/not_found'); return; }

        $submitted   = $this->post('answers', []);
        $dbQuestions = $this->model->getQuestionsByQuizId($quizId);

        // Validation
        $errors = [];
        foreach ($dbQuestions as $q) {
            if (empty($submitted[$q['id']])) {
                $errors[] = 'Please answer all questions before submitting.';
                break;
            }
        }

        if (!empty($errors)) {
            $rows      = $this->model->getQuestionsWithShuffledAnswers($quizId);
            $questions = [];
            foreach ($rows as $row) {
                $qid = $row['q_id'];
                if (!isset($questions[$qid])) {
                    $questions[$qid] = ['id' => $qid, 'question_text' => $row['question_text'], 'answers' => []];
                }
                $questions[$qid]['answers'][] = ['id' => $row['a_id'], 'answer_text' => $row['answer_text']];
            }
            $this->view('front/play', compact('quiz', 'questions', 'errors', 'submitted'));
            return;
        }

        // Grade
        $score   = 0;
        $results = [];

        foreach ($dbQuestions as $q) {
            $qId         = $q['id'];
            $submittedId = isset($submitted[$qId]) ? (int) $submitted[$qId] : null;

            $correctAnswer = $this->model->getCorrectAnswer($qId);
            $chosenRow     = $submittedId ? $this->model->getAnswerById($submittedId) : null;
            $chosenText    = $chosenRow['answer_text'] ?? null;

            $isCorrect = $correctAnswer && $submittedId === (int) $correctAnswer['id'];
            if ($isCorrect) $score++;

            $results[] = [
                'question_text'       => $q['question_text'],
                'chosen_answer_text'  => $chosenText,
                'correct_answer_text' => $correctAnswer['answer_text'] ?? 'N/A',
                'is_correct'          => $isCorrect,
                'skipped'             => $submittedId === null,
            ];
        }

        $this->view('front/result', [
            'quiz'    => $quiz,
            'score'   => $score,
            'total'   => count($dbQuestions),
            'results' => $results,
        ]);
    }

    // ── POST /front/feedback ──────────────────────────────────────────────────

    public function saveFeedback(): void {
        if (!$this->isPost()) { $this->redirect('index.php?route=/front'); }

        $quizId   = (int) $this->post('quiz_id');
        $userId   = (int) $this->post('user_id');
        $feedback = trim($this->post('feedback', ''));
        $score    = (int) $this->post('score');
        $total    = (int) $this->post('total');

        $errors = [];
        if (empty($feedback))      $errors[] = 'Feedback cannot be empty.';
        if (mb_strlen($feedback) > 1000) $errors[] = 'Feedback must be under 1000 characters.';
        if ($userId < 1)           $errors[] = 'User ID must be a positive number.';

        $quiz = $this->model->getQuizById($quizId);
        if (!$quiz) { $this->view('front/not_found'); return; }

        if (!empty($errors)) {
            // Re-render result page with errors and the feedback form still open
            $this->view('front/result', [
                'quiz'            => $quiz,
                'score'           => $score,
                'total'           => $total,
                'results'         => [],
                'feedbackErrors'  => $errors,
                'feedbackText'    => $feedback,
                'userId'          => $userId,
            ]);
            return;
        }

        $this->model->insertFeedback($quizId, $userId, $feedback);

        $this->redirect(
            'index.php?route=/front/play&id=' . $quizId . '&msg=feedback_sent'
        );
    }
}
