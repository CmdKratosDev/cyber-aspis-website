<?php
// CLI-Test fuer normalizeService() in contact.php. Kein Mailversand, kein vendor/ noetig.
// Aufruf: php tests/contact-whitelist-test.php
define('CONTACT_PHP_TEST_MODE', true);
require __DIR__ . '/../contact.php';

$fail = 0;
function check(string $label, mixed $got, mixed $want): void
{
    global $fail;
    if ($got === $want) {
        echo "ok   $label\n";
        return;
    }
    $fail++;
    echo "FAIL $label: got " . var_export($got, true) . ", want " . var_export($want, true) . "\n";
}

// 1) Whitelist == <option>-Werte in index.html (Drift-Wache)
$html = file_get_contents(__DIR__ . '/../index.html');
preg_match('/<select[^>]*id="cf-service".*?<\/select>/s', $html, $m);
preg_match_all('/<option value="([^"]*)"/', $m[0], $opts);
$formValues = array_values(array_filter(array_map('html_entity_decode', $opts[1]), fn($v) => $v !== ''));
check('whitelist == form options', CONTACT_SERVICE_WHITELIST, $formValues);

// 2) Jeder Formularwert bleibt unveraendert
foreach ($formValues as $v) {
    check("keeps '$v'", normalizeService($v), $v);
}
check('keeps value after trim', normalizeService("  Penetrationstest \t"), 'Penetrationstest');

// 3) Leer / Platzhalter / falscher Typ
check('empty', normalizeService(''), '');
check('whitespace only', normalizeService("  \r\n "), '');
check('frontend placeholder', normalizeService('Nicht angegeben'), '');
check('missing (null)', normalizeService(null), '');
check('array', normalizeService(['x']), '');
check('int', normalizeService(5), '');

// 4) Unbekanntes -> Sonstiges (kein Abweisen)
check('unknown', normalizeService('Irgendwas'), 'Sonstiges');
check('case differs', normalizeService('penetrationstest'), 'Sonstiges');
check('CRLF injection', normalizeService("Penetrationstest\r\nBcc: evil@example.com"), 'Sonstiges');
check('LF injection', normalizeService("x\nSubject: y"), 'Sonstiges');
check('html', normalizeService('<script>alert(1)</script>'), 'Sonstiges');
check('long', normalizeService(str_repeat('A', 100000)), 'Sonstiges');

echo $fail === 0 ? "ALL OK\n" : "$fail FAILED\n";
exit($fail === 0 ? 0 : 1);
