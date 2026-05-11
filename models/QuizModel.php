<?php

class QuizModel extends Model {

    // ── Quiz ──────────────────────────────────────────────────────────────────

    public function getAllQuizzes(): array {
        return $this->pdo
            ->query("SELECT id, type, description FROM quiz ORDER BY id ASC")
            ->fetchAll();
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

    public function getQuizById(int $id): array|false {
        $stmt = $this->pdo->prepare("SELECT * FROM quiz WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function insertQuiz(string $type, string $description): int {
        $stmt = $this->pdo->prepare("INSERT INTO quiz (type, description) VALUES (?, ?)");
        $stmt->execute([$type, $description]);
        return (int) $this->pdo->lastInsertId();
    }

    public function updateQuiz(int $id, string $type, string $description): void {
        $stmt = $this->pdo->prepare("UPDATE quiz SET type = ?, description = ? WHERE id = ?");
        $stmt->execute([$type, $description, $id]);
    }

    public function deleteQuiz(int $id): void {
        $stmt = $this->pdo->prepare("DELETE FROM quiz WHERE id = ?");
        $stmt->execute([$id]);
    }

    // ── Questions ─────────────────────────────────────────────────────────────

    public function getAllQuizzesWithQuestions(): array {
        $stmt = $this->pdo->query(
            "SELECT q.id AS quiz_id, q.type,
                    qu.id AS question_id, qu.question_text
             FROM quiz q
             LEFT JOIN questions qu ON q.id = qu.quiz_id
             ORDER BY q.id ASC"
        );
        return $stmt->fetchAll();
    }

    public function getQuestionsWithAnswers(int $quizId): array {
        $stmt = $this->pdo->prepare(
            "SELECT q.id AS q_id, q.question_text,
                    a.id AS a_id, a.answer_text, a.is_correct
             FROM questions q
             JOIN answers a ON q.id = a.question_id
             WHERE q.quiz_id = ?
             ORDER BY q.id ASC, a.id ASC"
        );
        $stmt->execute([$quizId]);
        return $stmt->fetchAll();
    }

    public function insertQuestion(int $quizId, string $text, int $points = 1): int {
        $stmt = $this->pdo->prepare(
            "INSERT INTO questions (quiz_id, question_text, points) VALUES (?, ?, ?)"
        );
        $stmt->execute([$quizId, $text, $points]);
        return (int) $this->pdo->lastInsertId();
    }

    public function updateQuestion(int $id, string $text): void {
        $stmt = $this->pdo->prepare("UPDATE questions SET question_text = ? WHERE id = ?");
        $stmt->execute([$text, $id]);
    }

    public function deleteQuestion(int $id): void {
        $stmt = $this->pdo->prepare("DELETE FROM questions WHERE id = ?");
        $stmt->execute([$id]);
    }

    // ── Answers ───────────────────────────────────────────────────────────────

    public function insertAnswer(int $questionId, string $text, bool $isCorrect): void {
        $stmt = $this->pdo->prepare(
            "INSERT INTO answers (question_id, answer_text, is_correct) VALUES (?, ?, ?)"
        );
        $stmt->execute([$questionId, $text, (int) $isCorrect]);
    }

    public function updateAnswer(int $id, string $text, bool $isCorrect): void {
        $stmt = $this->pdo->prepare(
            "UPDATE answers SET answer_text = ?, is_correct = ? WHERE id = ?"
        );
        $stmt->execute([$text, (int) $isCorrect, $id]);
    }

    // ── Transactions ──────────────────────────────────────────────────────────

    public function beginTransaction(): void   { $this->pdo->beginTransaction(); }
    public function commit(): void             { $this->pdo->commit(); }
    public function rollBack(): void           { $this->pdo->rollBack(); }

    // ── Feedback ──────────────────────────────────────────────────────────────

    public function getAllFeedback(): array {
        return $this->pdo->query(
            "SELECT f.*, q.type AS quiz_type
             FROM feedback f
             JOIN quiz q ON f.quizId = q.id
             ORDER BY f.id DESC"
        )->fetchAll();
    }

    // ── Results (read-only, for back office) ──────────────────────────────────

    public function getAllResults(): array {
        return $this->pdo->query(
            "SELECT r.id, r.score, r.total, r.passed_at,
                    q.type AS quiz_type,
                    u.prenom, u.nom, u.email
             FROM quiz_result r
             JOIN quiz q ON r.quiz_id = q.id
             JOIN utilisateur u ON r.user_id = u.idUtilisateur
             ORDER BY r.passed_at DESC"
        )->fetchAll();
    }

    public function getAllFeedback(): array {
        return $this->pdo->query(
            "SELECT f.id, f.feedback, f.userId,
                    q.type AS quiz_type,
                    u.prenom, u.nom
             FROM feedback f
             JOIN quiz q ON f.quizId = q.id
             LEFT JOIN utilisateur u ON f.userId = u.idUtilisateur
             ORDER BY f.id DESC"
        )->fetchAll();
    }
}
