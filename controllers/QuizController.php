<?php

class QuizController extends Controller {

    private QuizModel $model;

    public function __construct() {
        $this->model = new QuizModel();
    }

    // ── Dashboard ─────────────────────────────────────────────────────────────

    public function index(): void {
        $this->view('layouts/main');
    }

    // ── List ──────────────────────────────────────────────────────────────────

    public function list(): void {
        $search  = trim($this->get('search', ''));
        $sortCol = $this->get('sort', 'id');
        $sortDir = $this->get('dir', 'ASC');

        $quizzes = $this->model->searchAndSort($search, $sortCol, $sortDir);

        $this->view('quiz/list', compact('quizzes', 'search', 'sortCol', 'sortDir'));
    }

    // ── Show ──────────────────────────────────────────────────────────────────

    public function show(): void {
        $id   = (int) $this->get('id');
        $quiz = $this->model->getQuizById($id);

        if (!$quiz) { $this->view('quiz/not_found'); return; }

        $questions = $this->groupQuestionsWithAnswers(
            $this->model->getQuestionsWithAnswers($id)
        );

        $this->view('quiz/show', compact('quiz', 'questions'));
    }

    // ── Create (step 1) ───────────────────────────────────────────────────────

    public function create(): void {
        $this->view('quiz/create');
    }

    // ── Build form (step 2) ───────────────────────────────────────────────────

    public function buildForm(): void {
        if (!$this->isPost()) { $this->redirect('index.php?route=/quizzes/create'); }

        $type = trim($this->post('type', ''));
        $des  = trim($this->post('des', ''));
        $nb   = (int) $this->post('nb', 0);

        $errors = [];
        if (empty($type)) $errors[] = 'Quiz type is required.';
        if (empty($des))  $errors[] = 'Description is required.';
        if ($nb < 1)      $errors[] = 'At least 1 question is required.';

        if (!empty($errors)) {
            $this->view('quiz/create', compact('errors', 'type', 'des', 'nb'));
            return;
        }

        $this->view('quiz/create_questions', compact('type', 'des', 'nb'));
    }

    // ── Store (save new quiz) ─────────────────────────────────────────────────

    public function store(): void {
        if (!$this->isPost()) { $this->redirect('index.php?route=/quizzes/create'); }

        $type      = trim($this->post('type', ''));
        $des       = trim($this->post('des', ''));
        $nb        = (int) $this->post('nb', 0);
        $questions = $this->post('questions', []);

        $errors = $this->validateQuizForm($type, $des, $nb, $questions);

        if (!empty($errors)) {
            $this->view('quiz/create_questions', compact('type', 'des', 'nb', 'questions', 'errors'));
            return;
        }

        // Transaction orchestrated here in the controller
        $this->model->beginTransaction();
        try {
            $quizId = $this->model->insertQuiz($type, $des);

            foreach ($questions as $index => $qData) {
                $questionId   = $this->model->insertQuestion($quizId, $qData['text']);
                $correctIndex = (int) ($qData['correct'] ?? 0);

                foreach ($qData['answers'] as $aIndex => $aData) {
                    $this->model->insertAnswer($questionId, $aData['text'], $aIndex === $correctIndex);
                }
            }

            $this->model->commit();
            $this->redirect('index.php?route=/quizzes&msg=created');

        } catch (Exception $e) {
            $this->model->rollBack();
            $errors = ['Database error: ' . $e->getMessage()];
            $this->view('quiz/create_questions', compact('type', 'des', 'nb', 'questions', 'errors'));
        }
    }

    // ── Edit ──────────────────────────────────────────────────────────────────

    public function edit(): void {
        $id   = (int) $this->get('id');
        $quiz = $this->model->getQuizById($id);

        if (!$quiz) { $this->view('quiz/not_found'); return; }

        $questions = $this->groupQuestionsWithAnswers(
            $this->model->getQuestionsWithAnswers($id)
        );

        $this->view('quiz/edit', compact('quiz', 'questions'));
    }

    // ── Update (save edited quiz) ─────────────────────────────────────────────

    public function update(): void {
        if (!$this->isPost()) { $this->redirect('index.php?route=/quizzes/update-list'); }

        $quizId      = (int) $this->post('quiz_id');
        $type        = trim($this->post('type', ''));
        $description = trim($this->post('description', ''));
        $questions   = $this->post('questions', []);

        $correctAnswers = [];
        foreach ($questions as $qId => $qData) {
            $correctAnswers[$qId] = (int) ($_POST["correct_for_q_{$qId}"] ?? 0);
        }

        $errors = [];
        if (empty($type))        $errors[] = 'Quiz type is required.';
        if (empty($description)) $errors[] = 'Description is required.';

        if (!empty($errors)) {
            $quiz      = $this->model->getQuizById($quizId);
            $questions = $this->groupQuestionsWithAnswers(
                $this->model->getQuestionsWithAnswers($quizId)
            );
            $this->view('quiz/edit', compact('quiz', 'questions', 'errors'));
            return;
        }

        // Transaction orchestrated here in the controller
        $this->model->beginTransaction();
        try {
            $this->model->updateQuiz($quizId, $type, $description);

            foreach ($questions as $qId => $qData) {
                $this->model->updateQuestion((int) $qId, $qData['text']);
                $correctAnswerId = (int) ($correctAnswers[$qId] ?? 0);

                foreach ($qData['answers'] as $aId => $aData) {
                    $this->model->updateAnswer(
                        (int) $aId,
                        $aData['text'],
                        (int) $aId === $correctAnswerId
                    );
                }
            }

            $this->model->commit();
            $this->redirect('index.php?route=/quizzes&msg=updated');

        } catch (Exception $e) {
            $this->model->rollBack();
            $quiz      = $this->model->getQuizById($quizId);
            $questions = $this->groupQuestionsWithAnswers(
                $this->model->getQuestionsWithAnswers($quizId)
            );
            $errors = ['Database error: ' . $e->getMessage()];
            $this->view('quiz/edit', compact('quiz', 'questions', 'errors'));
        }
    }

    // ── Update list ───────────────────────────────────────────────────────────

    public function updateList(): void {
        $search  = trim($this->get('search', ''));
        $sortCol = $this->get('sort', 'id');
        $sortDir = $this->get('dir', 'ASC');

        $quizzes = $this->model->searchAndSort($search, $sortCol, $sortDir);

        $this->view('quiz/update_list', compact('quizzes', 'search', 'sortCol', 'sortDir'));
    }

    // ── Delete list ───────────────────────────────────────────────────────────

    public function deleteList(): void {
        $rows    = $this->model->getAllQuizzesWithQuestions();
        $quizzes = [];

        foreach ($rows as $row) {
            $qid = $row['quiz_id'];
            if (!isset($quizzes[$qid])) {
                $quizzes[$qid] = ['id' => $qid, 'type' => $row['type'], 'questions' => []];
            }
            if (!empty($row['question_id'])) {
                $quizzes[$qid]['questions'][] = [
                    'id'   => $row['question_id'],
                    'text' => $row['question_text'],
                ];
            }
        }

        $this->view('quiz/delete_list', compact('quizzes'));
    }

    // ── Delete ────────────────────────────────────────────────────────────────

    public function delete(): void {
        $type = $this->get('type');
        $id   = (int) $this->get('id');

        if (!$type || !$id) die('Invalid delete request.');

        try {
            if ($type === 'quiz')         $this->model->deleteQuiz($id);
            elseif ($type === 'question') $this->model->deleteQuestion($id);

            $this->redirect('index.php?route=/quizzes/delete-list&msg=deleted');
        } catch (Exception $e) {
            die('Delete failed: ' . htmlspecialchars($e->getMessage()));
        }
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    /**
     * Groups flat JOIN rows (from model) into a nested questions/answers array.
     * Grouping logic belongs in the controller, not the model.
     */
    private function groupQuestionsWithAnswers(array $rows): array {
        $questions = [];
        foreach ($rows as $row) {
            $qid = $row['q_id'];
            if (!isset($questions[$qid])) {
                $questions[$qid] = [
                    'id'      => $qid,
                    'text'    => $row['question_text'],
                    'answers' => [],
                ];
            }
            $questions[$qid]['answers'][] = [
                'id'         => $row['a_id'],
                'text'       => $row['answer_text'],
                'is_correct' => (bool) $row['is_correct'],
            ];
        }
        return $questions;
    }

    private function validateQuizForm(string $type, string $des, int $nb, array $questions): array {
        $errors = [];
        if (empty($type)) $errors[] = 'Quiz type is required.';
        if (empty($des))  $errors[] = 'Description is required.';
        if ($nb < 1)      $errors[] = 'At least one question is required.';

        foreach ($questions as $i => $q) {
            if (empty(trim($q['text'] ?? '')))
                $errors[] = 'Question ' . ($i + 1) . ' text is required.';
            if (!isset($q['correct']))
                $errors[] = 'Question ' . ($i + 1) . ' must have a correct answer selected.';
            foreach (($q['answers'] ?? []) as $j => $ans)
                if (empty(trim($ans['text'] ?? '')))
                    $errors[] = 'Q' . ($i + 1) . ' – Answer ' . ($j + 1) . ' cannot be empty.';
        }
        return $errors;
    }

    // ── Feedback list (back office) ───────────────────────────────────────────

    public function feedbackList(): void {
        $feedbacks = $this->model->getAllFeedback();
        $this->view('quiz/feedback_list', compact('feedbacks'));
    }

    public function resultsList(): void {
        $results = $this->model->getAllResults();
        $this->view('quiz/results_list', compact('results'));
    }
}
