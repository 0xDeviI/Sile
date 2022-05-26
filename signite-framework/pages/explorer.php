<?php

require_once "signite-framework/modules/PathTool.php";

use function Signite\Modules\getDirectoriesInDirectory;
use function Signite\Modules\getFilesInDirectory;
use function Signite\Modules\generatePathInArray;

$dirToSearch = "{{directoryToSearch}}";

$files = array_merge(getDirectoriesInDirectory($dirToSearch), getFilesInDirectory($dirToSearch));

?>


<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="/signite-framework/pages/resources/css/explorer.css">
    <script src="/signite-framework/pages/resources/js/explorer.js" defer=""></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css" referrerpolicy="no-referrer" />
    <title>{{directoryToSearch}}</title>
    <link rel="icon" href="/signite-framework/resources/images/signite-logo.ico">
</head>

<body>
    <div class="light" x-data="application" x-effect="storeThemeCookie(theme)" :class="{ 'light': theme === 'light' }">
        <div class="flex flex-col min-h-screen font-sans dark:bg-gray-800">
            <header class="bg-blue-600 shadow sticky top-0 dark:bg-purple-700">
                <div class="border-b border-blue-700 dark:border-purple-800">
                    <div class="container flex flex-wrap justify-between items-center space-x-6 mx-auto p-4 md:flex-row xl:max-w-screen-xl">
                        <a href="." class="flex items-center space-x-2 p-1" title="Logo">
                            <img src="/signite-framework/resources/images/signite-logo-light.png" alt="Logo" width="70" height="70">
                        </a>
                    </div>
                </div>

                <div class="border-t border-blue-500 dark:border-purple-600">
                    <div class="container flex flex-wrap justify-between items-center space-x-6 mx-auto px-4 py-1 md:flex-row xl:max-w-screen-xl">
                        <div class="flex-1 font-mono text-white text-sm tracking-tight overflow-x-auto whitespace-nowrap py-1">
                            <?php

                            $pathArray = explode("/", $dirToSearch);
                            $pathArray = array_filter($pathArray);
                            for ($i = 0; $i < count($pathArray); $i++) {
                                if ($pathArray[$i] == "") {
                                    continue;
                                }
                                else{
                                    if ($i == count($pathArray) - 1) {
                                        echo '
                                        <a style="font-weight: bold;" href="' . generatePathInArray($pathArray[$i], $pathArray) . '" class="inline-block hover:underline">
                                            ' . $pathArray[$i] . '
                                        </a>';
                                    }
                                    else{
                                        echo '
                                        <a style="font-weight: bold;" href="' . generatePathInArray($pathArray[$i], $pathArray) . '" class="inline-block hover:underline">
                                            ' . $pathArray[$i] . '
                                        </a>
                                        /';
                                    }
                                }
                            }

                            ?>
                        </div>
                    </div>
                </div>
            </header>

            <div class="flex flex-col flex-grow container mx-auto px-4 xl:max-w-screen-xl dark:text-white">
                <div class="my-4">
                    <div class="flex justify-between font-bold p-4">
                        <div class="flex-grow font-mono mr-2">
                            File Name
                        </div>

                        <div class="font-mono text-right w-1/6 mx-2 hidden sm:block">
                            Size
                        </div>

                        <div class="font-mono text-right w-1/4 ml-2 hidden sm:block">
                            Date
                        </div>
                    </div>

                    <ul>
                        <li>
                        </li>

                        <li>
                            <?php

                            foreach ($files as $file) {
                                if ($file["is_dir"]) {
                                    echo '
                                    <a href="' . $file["path"] . '" class="flex flex-col items-center rounded-lg font-mono group hover:bg-gray-100 hover:shadow dark:hover:bg-purple-700">
                                <div class="flex justify-between items-center p-4 w-full">
                                    <div class="pr-2">
                                        <i class="fas fa-folder fa-fw fa-lg"></i>
                                    </div>

                                    <div class="flex-1 truncate">
                                    ' . $file["name"] . '
                                    </div>


                                    <div class="hidden whitespace-nowrap text-right mx-2 w-1/6 sm:block">
                                        —
                                    </div>

                                    <div class="hidden whitespace-nowrap text-right truncate ml-2 w-1/4 sm:block">
                                    ' . $file["time"] . '
                                    </div>
                                </div>
                            </a>
                                    ';
                                } else {
                                    echo '
                                    <a href="' . $file["path"] . '" class="flex flex-col items-center rounded-lg font-mono group hover:bg-gray-100 hover:shadow dark:hover:bg-purple-700">
                                <div class="flex justify-between items-center p-4 w-full">
                                    <div class="pr-2">
                                        <i class="fas fa-file fa-fw fa-lg"></i>
                                    </div>

                                    <div class="flex-1 truncate">
                                    ' . $file["name"] . '
                                    </div>


                                    <div class="hidden whitespace-nowrap text-right mx-2 w-1/6 sm:block">
                                    ' . $file["size"] . '
                                    </div>

                                    <div class="hidden whitespace-nowrap text-right truncate ml-2 w-1/4 sm:block">
                                    ' . $file["time"] . '
                                    </div>
                                </div>
                            </a>
                                    ';
                                }
                            }

                            ?>

                        </li>
                    </ul>
                </div>


            </div>

            <div class="fixed bottom-0 left-0 right-0 pointer-events-none" x-data="{ visible: false }" x-init="visible = window.scrollY > 10" x-show="visible" x-transition.opacity="" style="display: none;">
                <div class="container flex justify-end mx-auto px-4 py-10 xl:max-w-screen-xl">
                    <button title="Scroll to Top" class="flex justify-center items-center w-12 h-12 right-0 rounded-full shadow-lg bg-blue-600 text-white cursor-pointer pointer-events-auto hover:bg-blue-700 dark:bg-purple-700 dark:hover:bg-purple-800" @click="window.scrollTo({ top: 0, left: 0, behavior: 'smooth' });" @scroll.window="visible = window.scrollY > 10">
                        <i class="fas fa-arrow-up fa-lg"></i>
                    </button>
                </div>
            </div>

            <div class="fixed inset-0 flex justify-center items-center bg-gray-800 bg-opacity-50 p-4 z-50 dark:bg-gray-600 dark:bg-opacity-50" x-data="fileInfoModal" x-show="visible" @click.self="hide()" @keyup.escape.window="hide()" @show-file-info.window="show($event.detail.file)" x-transition:enter.opacity="" style="display: none;">
                <div x-show="!loading" x-transition:enter="" @click.away="hide()" class="bg-white rounded-lg shadow-lg overflow-hidden dark:bg-gray-800 dark:text-white" style="display: none;">
                    <header class="flex justify-between items-center bg-blue-600 p-4 dark:bg-purple-700">
                        <i class="fas fa-info-circle fa-lg text-white"></i>

                        <div class="items-center text-xl text-white font-mono mx-4" x-text="title">file.info</div>

                        <button @click="hide()" class="flex justify-center items-center rounded-full w-6 h-6 text-gray-900 text-opacity-50 text-sm hover:bg-red-700 hover:text-white hover:shadow">
                            <i class="fas fa-times"></i>
                        </button>
                    </header>

                    <content class="flex justify-center items-center p-4">
                        <div class="overflow-x-auto">
                            <p class="font-thin text-2xl text-gray-600 m-4" x-if="error" x-text="error"></p>

                            <table x-if="error === undefined" class="table-auto">
                                <tbody>
                                    <template x-for="[name, value] in Object.entries(hashes)" :key="name">
                                        <tr>
                                            <td class="border font-bold px-4 py-2" x-text="name"></td>
                                            <td class="border font-mono px-4 py-2" x-text="value"></td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </content>
                </div>

                <i class="fas fa-spinner fa-pulse fa-5x text-white" x-show="loading"></i>
            </div>
        </div>
    </div>
</body>

</html>