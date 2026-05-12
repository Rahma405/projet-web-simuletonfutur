<?php

class FrontController extends Controller {

    private FrontModel $model;

    public function __construct() {
        $this->model = new FrontModel();
    }

    // ── Helper: get logged-in user ID from session (null if guest) ────────────
    private function sessionUserId(): ?int {
        $user = $_SESSION['user'] ?? null;
        if (!$user) return null;
        // Support both id and idUtilisateur key depending on login method
        return (int) ($user['id'] ?? $user['idUtilisateur'] ?? 0) ?: null;
    }

    // ── GET /front ────────────────────────────────────────────────────────────
    public function home(): void {
        $search  = trim($this->get('search', ''));
        $sortCol = $this->get('sort', 'id');
        $sortDir = $this->get('dir', 'ASC');

        $quizzes = $this->model->searchAndSort($search, $sortCol, $sortDir);

        $this->view('front/home', compact('quizzes', 'search', 'sortCol', 'sortDir'));
    }

    // ── GET /front/play?id=X ─────────────────────────────────────────────────
    public function play(): void {
        // Must be logged in to take a quiz
        if ($this->sessionUserId() === null) {
            $_SESSION['intended'] = 'index.php?route=/front/play&id=' . (int) $this->get('id');
            $this->redirect('index.php?route=/login');
            return;
        }

        $id   = (int) $this->get('id');
        $quiz = $this->model->getQuizById($id);

        if (!$quiz) { $this->view('front/not_found'); return; }

        $rows      = $this->model->getQuestionsWithShuffledAnswers($id);
        $questions = [];
        foreach ($rows as $row) {
            $qid = $row['q_id'];
            if (!isset($questions[$qid])) {
                $questions[$qid] = [
                    'id'            => $qid,
                    'question_text' => $row['question_text'],
                    'answers'       => [],
                ];
            }
            $questions[$qid]['answers'][] = [
                'id'          => $row['a_id'],
                'answer_text' => $row['answer_text'],
            ];
        }

        if (empty($questions)) {
            $this->view('front/empty_quiz', compact('quiz'));
            return;
        }

        $this->view('front/play', compact('quiz', 'questions'));
    }

    // ── POST /front/submit ────────────────────────────────────────────────────
    public function submit(): void {
        if (!$this->isPost()) { $this->redirect('index.php?route=/front'); }

        $quizId      = (int) $this->post('quiz_id');
        $quiz        = $this->model->getQuizById($quizId);

        if (!$quiz) { $this->view('front/not_found'); return; }

        $submitted   = $this->post('answers', []);
        $dbQuestions = $this->model->getQuestionsByQuizId($quizId);

        // Validate all questions answered
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
                    $questions[$qid] = [
                        'id'            => $qid,
                        'question_text' => $row['question_text'],
                        'answers'       => [],
                    ];
                }
                $questions[$qid]['answers'][] = [
                    'id'          => $row['a_id'],
                    'answer_text' => $row['answer_text'],
                ];
            }
            $this->view('front/play', compact('quiz', 'questions', 'errors', 'submitted'));
            return;
        }

        // Grade answers
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

        $total = count($dbQuestions);

        // ── Auto-save result if user is logged in ─────────────────────────────
        $userId    = $this->sessionUserId();
        $resultId  = null;

        if ($userId !== null) {
            $resultId = $this->model->insertResult($quizId, $userId, $score, $total);
        }

        $this->view('front/result', compact(
            'quiz', 'score', 'total', 'results', 'userId', 'resultId'
        ));
    }

    // ── POST /front/feedback ──────────────────────────────────────────────────
    public function saveFeedback(): void {
        if (!$this->isPost()) { $this->redirect('index.php?route=/front'); }

        $quizId   = (int) $this->post('quiz_id');
        $score    = (int) $this->post('score');
        $total    = (int) $this->post('total');
        $feedback = trim($this->post('feedback', ''));

        // User ID comes from session — never from a form field
        $userId = $this->sessionUserId();

        $quiz = $this->model->getQuizById($quizId);
        if (!$quiz) { $this->view('front/not_found'); return; }

        $errors = [];
        if (empty($feedback))           $errors[] = 'Feedback cannot be empty.';
        if (mb_strlen($feedback) > 1000) $errors[] = 'Feedback must be under 1000 characters.';
        if ($userId === null)            $errors[] = 'You must be logged in to leave feedback.';

        if (!empty($errors)) {
            $this->view('front/result', [
                'quiz'           => $quiz,
                'score'          => $score,
                'total'          => $total,
                'results'        => [],
                'userId'         => $userId,
                'feedbackErrors' => $errors,
                'feedbackText'   => $feedback,
            ]);
            return;
        }

        $this->model->insertFeedback($quizId, $userId, $feedback);
        $this->redirect('index.php?route=/front&msg=feedback_sent');
    }

    // ── GET /front/my-results ─────────────────────────────────────────────────
    public function myResults(): void {
        $userId = $this->sessionUserId();

        if ($userId === null) {
            $this->redirect('index.php?route=/login');
            return;
        }

        $results     = $this->model->getResultsByUser($userId);
        $sessionUser = $_SESSION['user'];

        $this->view('front/my_results', compact('results', 'sessionUser'));
    }
}
