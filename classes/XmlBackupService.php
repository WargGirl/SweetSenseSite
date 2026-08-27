<?php

class XmlBackupService {
    private string $filePath;
    private $parser;
    private array $parsedUsers = [];
    private array $currentUser = [];
    private ?string $currentTag = null;

    public function __construct() {
        $this->filePath = dirname(__DIR__, 1) . '/storage/users.xml';
        if (!is_dir(dirname(__DIR__, 1) . '/storage')) {
            mkdir(dirname(__DIR__, 1) . '/storage', 0777, true);
        }
    }

    public function exportUsersToXml(PDO $pdo): void {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->formatOutput = true;

        if (file_exists($this->filePath)) {
            $dom->load($this->filePath);
            $root = $dom->documentElement;
            while ($root->hasChildNodes()) {
                $root->removeChild($root->firstChild);
            }
        } else {
            $root = $dom->createElement('users');
            $dom->appendChild($root);
        }

        $stmt = $pdo->query("SELECT id, username, email, display_name, role, status, created_at FROM users");
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($users as $u) {
            $userElem = $dom->createElement('user');
            $userElem->setAttribute('id', (string)$u['id']);
            
            $userElem->appendChild($dom->createElement('username', htmlspecialchars($u['username'])));
            $userElem->appendChild($dom->createElement('email', htmlspecialchars($u['email'])));
            $userElem->appendChild($dom->createElement('display_name', htmlspecialchars($u['display_name'] ?? '')));
            $userElem->appendChild($dom->createElement('role', htmlspecialchars($u['role'])));
            $userElem->appendChild($dom->createElement('status', htmlspecialchars($u['status'])));
            $userElem->appendChild($dom->createElement('created_at', htmlspecialchars($u['created_at'])));

            $root->appendChild($userElem);
        }

        $dom->save($this->filePath);
    }

    private function tagOpen($parser, string $name, array $attrs): void {
        $this->currentTag = strtolower($name);
        if ($this->currentTag === 'user') {
            $this->currentUser = ['id' => $attrs['ID'] ?? $attrs['id'] ?? ''];
        }
    }

    private function tagData($parser, string $data): void {
        $text = trim($data);
        if ($text !== '' && $this->currentTag !== null && $this->currentTag !== 'users' && $this->currentTag !== 'user') {
            $this->currentUser[$this->currentTag] = ($this->currentUser[$this->currentTag] ?? '') . $text;
        }
    }

    private function tagClose($parser, string $name): void {
        if (strtolower($name) === 'user') {
            $this->parsedUsers[] = $this->currentUser;
            $this->currentUser = [];
        }
        $this->currentTag = null;
    }

    public function parseAndRenderHtmlTable(): string {
        if (!file_exists($this->filePath)) {
            return '<div class="admin-feedback admin-feedback-error">Файл бекапу storage/users.xml ще не створено. Натисніть кнопку вище для експорту.</div>';
        }

        $this->parsedUsers = [];
        
        $this->parser = xml_parser_create('UTF-8');
        xml_parser_set_option($this->parser, XML_OPTION_CASE_FOLDING, 0);

        xml_set_element_handler($this->parser, [$this, 'tagOpen'], [$this, 'tagClose']);
        xml_set_character_data_handler($this->parser, [$this, 'tagData']);

        $fp = fopen($this->filePath, 'r');
        while ($chunk = fread($fp, 4096)) {
            xml_parse($this->parser, $chunk, feof($fp));
        }
        fclose($fp);

        if (empty($this->parsedUsers)) {
            return '<p>Бекап порожній.</p>';
        }

        $html = '<table class="admin-table table-xml">';
        $html .= '<thead><tr>';
        $html .= '<th class="col-u-id">ID</th>';
        $html .= '<th class="col-u-name">Username</th>';
        $html .= '<th>Display Name</th>';
        $html .= '<th>Email</th>';
        $html .= '<th class="col-u-status">Role / Status</th>';
        $html .= '<th class="col-u-date">Created At</th>';
        $html .= '</tr></thead><tbody>';

        foreach ($this->parsedUsers as $row) {
            $html .= '<tr>';
            $html .= '<td class="col-u-id">' . htmlspecialchars($row['id'] ?? '-') . '</td>';
            $html .= '<td class="col-u-name"><strong>@' . htmlspecialchars($row['username'] ?? '') . '</strong></td>';
            $html .= '<td>' . htmlspecialchars($row['display_name'] ?? '') . '</td>';
            $html .= '<td>' . htmlspecialchars($row['email'] ?? '') . '</td>';
            $html .= '<td class="col-u-status"><span class="badge badge-info">' . htmlspecialchars($row['role'] ?? '') . '</span> / ' . htmlspecialchars($row['status'] ?? '') . '</td>';
            $html .= '<td class="col-u-date">' . htmlspecialchars($row['created_at'] ?? '') . '</td>';
            $html .= '</tr>';
        }

        $html .= '</tbody></table>';
        return $html;
    }
}
