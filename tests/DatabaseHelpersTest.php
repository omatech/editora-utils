<?php
use Doctrine\DBAL\DriverManager;
use Omatech\Editora\Utils\Editora;
use Omatech\Editora\Utils\Strings;
use PHPUnit\Framework\TestCase;

final class DatabaseHelpersTest extends TestCase
{
    public function test_language_links_bind_the_instance_and_language_as_values(): void
    {
        $db = DriverManager::getConnection(['driver' => 'pdo_sqlite', 'memory' => true]);
        $db->executeStatement('CREATE TABLE omp_niceurl(inst_id INTEGER, language TEXT, niceurl TEXT)');
        $db->insert('omp_niceurl', ['inst_id' => 7, 'language' => 'ca', 'niceurl' => 'pagina']);
        $db->insert('omp_niceurl', ['inst_id' => 7, 'language' => 'es', 'niceurl' => 'pagina-es']);
        $db->insert('omp_niceurl', ['inst_id' => 9, 'language' => 'es', 'niceurl' => 'foreign']);
        new Editora($db);
        self::assertSame([['language' => 'es', 'niceurl' => 'pagina-es']], Editora::other_languages_url(7, 'ca'));
        self::assertSame([], Editora::other_languages_url('7 OR 1=1', 'ca'));
        self::assertCount(2, Editora::other_languages_url(7, "ca' OR 1=1 --"));
        $db->close();
    }

    public function test_utf8_strings_keep_whole_codepoints_and_stable_slugs(): void
    {
        self::assertSame('à😊', Strings::utf8_substr('xà😊z', 1, 2));
        self::assertSame('acces-public-42', Strings::urlnicify('Àccés públic', 42));
        self::assertSame('', Strings::urlnicify(''));
    }
}
