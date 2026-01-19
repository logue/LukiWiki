<?php

/**
 * コンポーサーコントローラー.
 *
 * @author    Logue <logue@hotmail.co.jp>
 * @copyright 2019 Logue
 * @license   MIT
 */

namespace App\Http\Controllers;

use bookin\composer\api\Composer;
use Illuminate\Http\Request;

class ComposerController extends Controller
{
    /** @var \bookin\composer\api\Composer */
    private static $composer;

    /**
     * Constructor.
     */
    public function __construct()
    {
        if (! self::$composer) {
            self::$composer = Composer::getInstance(base_path('composer.json'), base_path());
        }
    }

    /**
     * Installed Packages.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $result = [];
        foreach (self::$composer::getLocalPackages() as /* @var \Composer\Package\CompletePackageInterface */ $package) {
            $result[] = [
                'name' => $package->getName(),
                'version' => $package->getVersion(),
                'license' => $package->getLicense()[0],
                'author' => $package->getAuthors(),
                'description' => $package->getDescription(),
                'keywords' => $package->getKeywords(),
                'releaseDate' => $package->getReleaseDate()->format(\DateTime::ATOM),
                'url' => $package->getHomepage() ?? $package->getSourceUrl(),
            ];
        }

        return response($result);
    }

    /**
     * Search package.
     *
     * @return \Illuminate\Http\Response
     */
    public function search(Request $request)
    {
        return response($this->composer::searchPackage($request->input('query')));
    }

    /**
     * Execute Composer Command.
     *
     * @return \Illuminate\Http\Response
     */
    public function execute(Request $request)
    {
        $command = $request->input('command') ?? 'list';
        $options = $request->input('options') ?? [];

        return response(self::$composer::runCommand($command, $options));
    }
}
