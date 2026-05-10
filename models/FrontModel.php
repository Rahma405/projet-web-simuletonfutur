<?php

class FrontModel extends Model {

    // ── Catalogue ─────────────────────────────────────────────────────────────

    public function getAllQuizzesWithCount(): array {
        return $this->pdo->query(
            "SELECT q.id, q.type, q.description,
                    COUNT(qu.id) AS question_count
             FROM quiz q
             LEFT JOIN questions qu ON q.id = qu.quiz_id
             GROUP BY q.id
             ORDER BY q.id ASC"
        )->fetchAll();
    }

    public function searchAndSort(string $search, string $sortCol, string $sortDir): array {
        $cols = ['id' => 'q.id', 'type' => 'q.type', 'question_count' => 'question_count'];
        $col  = $cols[$sortCol] ?? 'q.id';
        $dir  = strtoupper($sortDir) === 'DESC' ? 'DESC' : 'ASC';

        $stmt = $this->pdo->prepare(
            "SELECT q.id, q.type, q.description,
                    COUNT(qu.id) AS question_count
             FROM quiz q
             LEFT JOIN questions qu ON q.id = qu.quiz_id
             WHERE q.type LIKE :s1 OR q.description LIKE :s2
             GROUP BY q.id
             ORDER BY $col $dir"
        );
        $like = '%' . $search . '%';
        $stmt->execute(['s1' => $like, 's2' => $like]);
        return $stmt->fetchAll();
    }

    // ── Quiz ──────────────────────────────────────────────────────────────────

    public function getQuizById(int $id): array|false {
        $stmt = $this->pdo->prepare("SELECT * FROM quiz WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    // ── Questions ─────────────────────────────────────────────────────────────

    public function getQuestionsByQuizId(int $quizId): array {
        $stmt = $this->pdo->prepare(
            "SELECT id, question_text FROM questions WHERE quiz_id = ? ORDER BY id ASC"
        );
        $stmt->execute([$quizId]);
        return $stmt->fetchAll();
    }

    public function getQuestionsWithShuffledAnswers(int $quizId): array {
        $stmt = $this->pdo->prepare(
            "SELECT q.id AS q_id, q.question_text,
                    a.id AS a_id, a.answer_text
             FROM questions q
             JOIN answers a ON q.id = a.question_id
             WHERE q.quiz_id = ?
             ORDER BY q.id ASC, RAND()"
        );
        $stmt->execute([$quizId]);
        return $stmt->fetchAll();
    }

    // ── Grading ───────────────────────────────────────────────────────────────

    public function getCorrectAnswer(int $questionId): array|false {
        $stmt = $this->pdo->prepare(
            "SELECT id, answer_text FROM answers WHERE question_id = ? AND is_correct = 1 LIMIT 1"
        );
        $stmt->execute([$questionId]);
        return $stmt->fetch();
    }

    public function getAnswerById(int $answerId): array|false {
        $stmt = $this->pdo->prepare("SELECT answer_text FROM answers WHERE id = ?");
        $stmt->execute([$answerId]);
        return $stmt->fetch();
    }

    // ── Feedback ──────────────────────────────────────────────────────────────

    public function insertFeedback(int $quizId, int $userId, string $feedback): void {
        $stmt = $this->pdo->prepare(
            "INSERT INTO feedback (quizId, userId, feedback) VALUES (?, ?, ?)"
        );
        $stmt->execute([$quizId, $userId, $feedback]);
    }

    public function getFeedbackByQuizId(int $quizId): array {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM feedback WHERE quizId = ? ORDER BY id DESC"
        );
        $stmt->execute([$quizId]);
        return $stmt->fetchAll();
    }

    public function getAllFeedback(): array {
        return $this->pdo->query(
            "SELECT f.*, q.type AS quiz_type
             FROM feedback f
             JOIN quiz q ON f.quizId = q.id
             ORDER BY f.id DESC"
        )->fetchAll();
    }
}
