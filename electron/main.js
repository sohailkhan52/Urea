import { app, BrowserWindow } from 'electron';
import { spawn } from 'child_process';
import path from 'path';
import fs from 'fs';
import http from 'http';

import {
    checkDatabaseExists,
    createDatabase,
    importDatabase
} from './database-setup.js';

let mysqlProcess = null;
let laravelProcess = null;


// ======================================================
// PATHS
// ======================================================

const projectPath = app.isPackaged
    ? path.join(process.resourcesPath, 'laravel')
    : process.cwd();

const runtimePath = app.isPackaged
    ? path.join(process.resourcesPath, 'runtime')
    : path.join(projectPath, 'runtime');

const userDataPath = app.getPath('userData');

const mysqlDataPath = path.join(
    userDataPath,
    'mysql-data'
);

if (!fs.existsSync(mysqlDataPath)) {
    fs.mkdirSync(mysqlDataPath, {
        recursive: true
    });
}


// ======================================================
// DATABASE SQL FILE
// ======================================================

const databaseSqlFile = app.isPackaged
    ? path.join(
        process.resourcesPath,
        'urea_database.sql'
    )
    : path.join(
        projectPath,
        'urea_database.sql'
    );


// ======================================================
// MYSQL PATHS
// ======================================================

const mysqlExe = path.join(
    runtimePath,
    'mysql',
    'bin',
    'mysqld.exe'
);

const mysqlAdmin = path.join(
    runtimePath,
    'mysql',
    'bin',
    'mysqladmin.exe'
);

const mysqlClient = path.join(
    runtimePath,
    'mysql',
    'bin',
    'mysql.exe'
);

const mysqlConfig = path.join(
    runtimePath,
    'mysql',
    'my.ini'
);

const mysqlBasePath = path.join(
    runtimePath,
    'mysql'
);


// ======================================================
// PHP
// ======================================================

const phpExe = path.join(
    runtimePath,
    'php',
    'php.exe'
);


// ======================================================
// INITIALIZE MYSQL IF REQUIRED
// ======================================================

async function initializeMySQLIfRequired() {

    console.log('========================================');
    console.log('Checking MySQL initialization...');
    console.log('========================================');

    const mysqlSystemDatabase = path.join(
        mysqlDataPath,
        'mysql'
    );

    // --------------------------------------------------
    // Already initialized
    // --------------------------------------------------

    if (fs.existsSync(mysqlSystemDatabase)) {

        console.log(
            'MySQL data directory is already initialized.'
        );

        return;
    }


    // --------------------------------------------------
    // Fresh installation
    // --------------------------------------------------

    console.log(
        'Fresh MySQL installation detected.'
    );


    // --------------------------------------------------
    // Handle incomplete initialization
    // --------------------------------------------------

    const existingFiles =
        fs.readdirSync(mysqlDataPath);

    if (existingFiles.length > 0) {

        console.log(
            'Incomplete MySQL data detected.'
        );

        console.log(
            'Existing files:',
            existingFiles
        );

        // Only remove the known harmless initialization
        // file when the mysql system database does not exist.
        //
        // This prevents MySQL from refusing initialization
        // because the data directory is not empty.

        for (const file of existingFiles) {

            const filePath =
                path.join(
                    mysqlDataPath,
                    file
                );

            if (
                file === 'auto.cnf' &&
                fs.existsSync(filePath)
            ) {

                console.log(
                    'Removing incomplete auto.cnf...'
                );

                fs.rmSync(
                    filePath,
                    {
                        force: true
                    }
                );
            }
        }
    }


    // --------------------------------------------------
    // Initialize MySQL
    // --------------------------------------------------

    console.log(
        'Initializing MySQL data directory...'
    );

    await runProcess(
        mysqlExe,
        [
            `--defaults-file=${mysqlConfig}`,
            `--basedir=${mysqlBasePath}`,
            '--initialize-insecure',
            `--datadir=${mysqlDataPath}`
        ],
        runtimePath
    );

    console.log(
        'MySQL initialization completed successfully.'
    );
}


// ======================================================
// START MYSQL
// ======================================================

function startMySQL() {

    console.log(
        'Starting bundled MySQL...'
    );

    mysqlProcess = spawn(
        mysqlExe,
        [
            `--defaults-file=${mysqlConfig}`,

            // Important for portable installation
            `--basedir=${mysqlBasePath}`,

            `--datadir=${mysqlDataPath}`,

            '--port=3308',

            '--bind-address=127.0.0.1',

            `--log-error=${path.join(
                mysqlDataPath,
                'mysql-error.log'
            )}`,

            '--console'
        ],
        {
            cwd: runtimePath,
            windowsHide: true
        }
    );


    mysqlProcess.stdout.on(
        'data',
        (data) => {

            console.log(
                `MySQL: ${data.toString()}`
            );

        }
    );


    mysqlProcess.stderr.on(
        'data',
        (data) => {

            console.error(
                `MySQL Error: ${data.toString()}`
            );

        }
    );


    mysqlProcess.on(
        'error',
        (error) => {

            console.error(
                'MySQL failed to start:',
                error
            );

        }
    );


    mysqlProcess.on(
        'exit',
        (code) => {

            console.log(
                `MySQL stopped with code: ${code}`
            );

        }
    );
}


// ======================================================
// WAIT FOR MYSQL
// ======================================================

async function waitForMySQL(
    maxAttempts = 30
) {

    for (
        let attempt = 1;
        attempt <= maxAttempts;
        attempt++
    ) {

        try {

            await new Promise(
                (resolve, reject) => {

                    const check =
                        spawn(
                            mysqlAdmin,
                            [
                                '-h',
                                '127.0.0.1',

                                '-P',
                                '3308',

                                '-u',
                                'root',

                                'ping'
                            ],
                            {
                                cwd: runtimePath,
                                windowsHide: true
                            }
                        );


                    check.on(
                        'exit',
                        (code) => {

                            if (code === 0) {

                                resolve();

                            } else {

                                reject(
                                    new Error(
                                        `MySQL not ready: ${code}`
                                    )
                                );

                            }

                        }
                    );


                    check.on(
                        'error',
                        reject
                    );

                }
            );


            console.log(
                'MySQL is ready.'
            );

            return true;

        } catch (error) {

            console.log(
                `Waiting for MySQL... attempt ${attempt}/${maxAttempts}`
            );

            await new Promise(
                (resolve) => {

                    setTimeout(
                        resolve,
                        1000
                    );

                }
            );
        }
    }


    console.error(
        'MySQL did not become ready.'
    );

    return false;
}


// ======================================================
// START LARAVEL
// ======================================================

function startLaravel() {

    console.log(
        'Starting Laravel...'
    );


    laravelProcess = spawn(
        phpExe,
        [
            'artisan',
            'serve',
            '--host=127.0.0.1',
            '--port=8000'
        ],
        {
            cwd: projectPath,

            windowsHide: true,

            env: {
                ...process.env,

                APP_ENV: 'production',

                APP_DEBUG: 'false',

                DB_HOST: '127.0.0.1',

                DB_PORT: '3308',

                DB_DATABASE: 'urea',

                DB_USERNAME: 'root',

                DB_PASSWORD: ''
            }
        }
    );


    laravelProcess.stdout.on(
        'data',
        (data) => {

            console.log(
                `Laravel: ${data.toString()}`
            );

        }
    );


    laravelProcess.stderr.on(
        'data',
        (data) => {

            console.error(
                `Laravel Error: ${data.toString()}`
            );

        }
    );


    laravelProcess.on(
        'error',
        (error) => {

            console.error(
                'Laravel failed to start:',
                error
            );

        }
    );


    laravelProcess.on(
        'exit',
        (code) => {

            console.log(
                `Laravel stopped with code: ${code}`
            );

        }
    );
}


// ======================================================
// WAIT FOR LARAVEL
// ======================================================

async function waitForLaravel(
    maxAttempts = 30
) {

    for (
        let attempt = 1;
        attempt <= maxAttempts;
        attempt++
    ) {

        const isReady =
            await new Promise(
                (resolve) => {

                    const request =
                        http.get(
                            'http://127.0.0.1:8000',
                            {
                                timeout: 1000
                            },
                            (response) => {

                                response.resume();

                                resolve(
                                    response.statusCode >= 200 &&
                                    response.statusCode < 500
                                );

                            }
                        );


                    request.on(
                        'error',
                        () => {

                            resolve(false);

                        }
                    );


                    request.on(
                        'timeout',
                        () => {

                            request.destroy();

                            resolve(false);

                        }
                    );

                }
            );


        if (isReady) {

            console.log(
                'Laravel is ready.'
            );

            return true;
        }


        console.log(
            `Waiting for Laravel... attempt ${attempt}/${maxAttempts}`
        );


        await new Promise(
            (resolve) => {

                setTimeout(
                    resolve,
                    1000
                );

            }
        );
    }


    console.error(
        'Laravel did not become ready.'
    );

    return false;
}


// ======================================================
// CREATE ELECTRON WINDOW
// ======================================================

function createWindow() {

    const win =
        new BrowserWindow({
            width: 1400,
            height: 900,

            webPreferences: {
                nodeIntegration: false,
                contextIsolation: true
            }
        });


    win.loadURL(
        'http://127.0.0.1:8000'
    );


    win.on(
        'closed',
        () => {
            // Window closed
        }
    );
}


// ======================================================
// STOP PROCESSES
// ======================================================

function stopProcesses() {

    if (laravelProcess) {

        try {

            spawn(
                'taskkill',
                [
                    '/F',
                    '/T',
                    '/PID',
                    String(laravelProcess.pid)
                ],
                {
                    windowsHide: true
                }
            );

        } catch (error) {

            console.error(
                'Failed to stop Laravel:',
                error
            );

        }

        laravelProcess = null;
    }


    if (mysqlProcess) {

        try {

            spawn(
                'taskkill',
                [
                    '/F',
                    '/T',
                    '/PID',
                    String(mysqlProcess.pid)
                ],
                {
                    windowsHide: true
                }
            );

        } catch (error) {

            console.error(
                'Failed to stop MySQL:',
                error
            );

        }

        mysqlProcess = null;
    }
}


// ======================================================
// STARTUP INFORMATION
// ======================================================

console.log(
    'USER DATA PATH:',
    userDataPath
);

console.log(
    'PROJECT PATH:',
    projectPath
);

console.log(
    'RUNTIME PATH:',
    runtimePath
);

console.log(
    'MYSQL DATA PATH:',
    mysqlDataPath
);

console.log(
    'DATABASE SQL FILE:',
    databaseSqlFile
);


// ======================================================
// APPLICATION STARTUP
// ======================================================

app.whenReady().then(
    async () => {

        try {

            // ------------------------------------------
            // STEP 1
            // Initialize MySQL if fresh installation
            // ------------------------------------------

            await initializeMySQLIfRequired();


            // ------------------------------------------
            // STEP 2
            // Start MySQL
            // ------------------------------------------

            startMySQL();


            // ------------------------------------------
            // STEP 3
            // Wait for MySQL
            // ------------------------------------------

            const mysqlReady =
                await waitForMySQL();


            if (!mysqlReady) {

                console.error(
                    'Application startup stopped because MySQL is not ready.'
                );

                return;
            }


            // ------------------------------------------
            // STEP 4
            // Setup database
            // ------------------------------------------

            const databaseExists =
                await checkDatabaseExists({
                    mysqlClient,
                    mysqlPort: 3308,
                    cwd: runtimePath
                });


            if (!databaseExists) {

                console.log(
                    'Urea database not found.'
                );


                await createDatabase({
                    mysqlClient,
                    mysqlPort: 3308,
                    cwd: runtimePath
                });


                await importDatabase({
                    mysqlClient,
                    databaseSqlFile,
                    mysqlPort: 3308,
                    cwd: runtimePath
                });


                console.log(
                    'Urea database setup completed successfully.'
                );

            } else {

                console.log(
                    'Urea database already exists. Skipping import.'
                );

            }


            // ------------------------------------------
            // STEP 5
            // Start Laravel
            // ------------------------------------------

            startLaravel();


            // ------------------------------------------
            // STEP 6
            // Wait for Laravel
            // ------------------------------------------

            const laravelReady =
                await waitForLaravel();


            if (!laravelReady) {

                console.error(
                    'Application startup stopped because Laravel is not ready.'
                );

                return;
            }


            // ------------------------------------------
            // STEP 7
            // Open application
            // ------------------------------------------

            createWindow();

        } catch (error) {

            console.error(
                'APPLICATION STARTUP FAILED:',
                error
            );

        }

    }
);


// ======================================================
// ELECTRON EVENTS
// ======================================================

app.on(
    'before-quit',
    () => {

        stopProcesses();

    }
);


app.on(
    'window-all-closed',
    () => {

        stopProcesses();

        if (process.platform !== 'darwin') {

            app.quit();

        }

    }
);


// ======================================================
// GENERIC PROCESS RUNNER
// ======================================================

function runProcess(
    executable,
    args,
    cwd
) {

    return new Promise(
        (resolve, reject) => {

            const child =
                spawn(
                    executable,
                    args,
                    {
                        cwd,
                        windowsHide: true
                    }
                );


            let output = '';
            let errorOutput = '';


            child.stdout.on(
                'data',
                (data) => {

                    output +=
                        data.toString();

                }
            );


            child.stderr.on(
                'data',
                (data) => {

                    errorOutput +=
                        data.toString();

                }
            );


            child.on(
                'error',
                (error) => {

                    reject(error);

                }
            );


            child.on(
                'exit',
                (code) => {

                    if (code === 0) {

                        resolve(output);

                    } else {

                        reject(
                            new Error(
                                `Process failed with code ${code}\n${errorOutput}`
                            )
                        );

                    }

                }
            );

        }
    );
}
