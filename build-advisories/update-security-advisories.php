<?php
/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license.
 */

declare(strict_types=1);

namespace Roave\SecurityAdvisories;

use ErrorException;

// Note: this script is responsible for handling incoming requests from the github push notifications,
// and to re-run the code generation/checks every time
(function () {
    set_error_handler(
        function ($errorCode, $message = '', $file = '', $line = 0) {
            throw new ErrorException($message, 0, $errorCode, $file, $line);
        },
        E_STRICT | E_NOTICE | E_WARNING
    );

    $runInPath = function (callable $function, $path) {
        $originalPath = getcwd();

        chdir($path);

        try {
            return $function();
        } finally {
            chdir($originalPath);
        }
    };

    $execute = function ($commandString) {
        // may the gods forgive me for this in-lined command addendum, but I CBA to fix proc_open's handling
        // of exit codes.
        exec($commandString . ' 2>&1', $output, $result);

        if (0 !== $result) {
            throw new \UnexpectedValueException(sprintf(
                'Command failed: "%s" "%s"',
                $commandString,
                implode(PHP_EOL, $output)
            ));
        }

        return $output;
    };

    $getCurrentSha1 = function () use ($runInPath, $execute) {
        return $runInPath(
            function () use ($execute) {
                return $execute('git rev-parse --verify HEAD')[0];
            },
            realpath(__DIR__ . '/..')
        );
    };

    $runInPath(
        function () use ($execute) {
            $execute('git fetch origin');
            $execute('git reset --hard origin/master');
        },
        realpath(__DIR__ . '/..')
    );

    $previousSha1 = $getCurrentSha1();

    $runInPath(
        function () use ($execute) {
            $execute(
                'curl -sS https://getcomposer.org/installer -o composer-installer.php && php composer-installer.php'
            );
            $execute('php composer.phar install');
        },
        realpath(__DIR__)
    );

    $runInPath(
        function () use ($execute) {
            $execute('php build-advisories/build-conflicts.php');
            $execute('git push origin master');
        },
        realpath(__DIR__ . '/..')
    );

    header('Content-Type: application/json');
    echo json_encode([
        'before' => $previousSha1,
        'after'  => $getCurrentSha1(),
    ]);
})();
