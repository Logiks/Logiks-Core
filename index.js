/*
 * This is a logiks node agent. 
 * 
 * It should not be run in user mode and not in adminstrative mode or root mode.
 * 
 * 
 * @author Bismay K Mohapatra bismay4u@gmail.com
 * @version 1.0
 * */

const os = require("os");
const fs = require("fs");
const path = require("path")
const axios = require('axios');
const moment = require('moment');
const validator = require('validatorjs');
const _ = require('lodash');
const express = require('express');
const http = require('http');

const _CONFIG = require("./config/jsonConfig/nodejs.json");
const _PACKAGE = require('./package.json');

_CONFIG.ROOT_DIR = __dirname+"/";
_CONFIG.ROOT_FOLDERNAME = path.basename(_CONFIG.ROOT_DIR);
_CONFIG.OS_TYPE = os.type().toUpperCase();
_CONFIG.START_TIME = moment().format();

if(_CONFIG.DEBUG==null) _CONFIG.DEBUG = false;


const app = express();
const server = http.createServer(app);

app.use(express.json());


const CMD_SET = {
	"ps": "ps -aux;"
};
const CMD_NOTALLOWED = ["sudo", "top"];


// fs.readdirSync('./api/').forEach(function (file) {
//     //   console.log("Loading api : " + filePath);
//     if ((file.indexOf(".js") > 0 && (file.indexOf(".js") + 3 == file.length))) {
//         filePath = path.resolve('./api/' + file);
//         CLSINDEX['API'][file.toUpperCase()] = require(filePath)(server, restify);
//     }
// });


process.on('uncaughtException', function (err) {
    console.error(err.name, err.message, err.stack);
});


//All Routes and Functionalities
app.get('/', (req, res) => {
  res.send({
    "SERVER": _PACKAGE.name,
    "VERSION": _PACKAGE.version,
    "TIMESTAMP": moment().format("Y-M-D HH:mm:ss")
  });
})

app.get('/stats', (req, res) => {
  res.send({
    "server": _PACKAGE.name,
    "server_version": _PACKAGE.version,
    "running_since": moment(_CONFIG.START_TIME).fromNow(),
    "timestamp": moment().format("Y-M-D HH:mm:ss"),
    "PLATFORM": os.platform(),
    "RELEASE": os.release(),
    "TYPE": os.type(),
    "ARCH": os.arch(),
    "cpus": os.cpus().length,

    "MEM_TOTAL": Math.floor((os.totalmem() / (1024 * 1024))) + " MB",
    "MEM_FREE": Math.floor((os.freemem() / (1024 * 1024))) + " MB",
    "MEM_PROCESS": Math.floor(process.memoryUsage().heapUsed / (1024 * 1024)) + " MB",
    
    "OS_LOADAVG": os.loadavg(),
    "OS_UPTIME": os.uptime(),
    "HOST": os.hostname(),
    // "USER": os.userInfo(),
    // "DIR_HOME": os.homedir(),
    // "DIR_TMP": os.tmpdir(),
    "ROOT_PATH": _CONFIG.ROOT_DIR,
  });
})

app.get('/restart', (req, res) => {
  res.send({
    "status": "success",
    "msg": "Check after 30 sec for status of server"
  });
  process.exit(0);
})

app.post('/run', (req, res) => {
  let isValid = new validator(req.body, {
      src: 'required|min:3',
      // path: 'required|min:3',
    });

  if(!isValid.passes()) {
    return res.send({
      "status": "error",
      "errors": isValid.errors,
      "msg": "Validation Failed"
    });
  }

  if(req.body.path==null) req.body.path = _CONFIG.ROOT_DIR;

  var base_path = req.body.path;
  var script = req.body.src;

  delete req.body.src;
  delete req.body.path;

  var ext = script.split(".");
  ext = ext[ext.length -1];

  var scriptFile = _CONFIG.SCRIPT_PATH+script;
  
  console.log(base_path, scriptFile, ext, req.body);


  try {
    const data = fs.readFileSync(scriptFile, 'utf8');

    switch(ext) {
      case "sh":
        var cmd = "cd " + base_path + ";echo \"At Path : `pwd`\";echo \"By User : `whoami`\n\";" + data;

        var http = require('http'),
            url = require('url'),
            exec = require('child_process').exec;
        
        var child = exec(cmd, function (error, stdout, stderr) {
                //stdout=stdout.split("\n");
                //var result = '{"stdout":' + stdout + ',"stderr":"' + stderr + '","cmd":"' + cmd + '"}';
                if (stderr == null || stderr.length <= 0) {
                  res.writeHead(200, {
                    'Content-Type': 'text/plain; charset=utf-8'
                  });
                  res.end(stdout + '\n');
                } else {
                  res.writeHead(500, {
                    'Content-Type': 'text/plain; charset=utf-8'
                  });
                  res.end(stderr+ '\n\n' + stdout + '\n');
                }
            });
        break;
      case "js":
        var x = console.log;
        var tempData = "";
        console.log = function(data) {
          tempData+=data;
        }
        var result = eval(data);
        console.log = x;
        res.send(tempData);
        break;
      default:
        res.send("Script Format Not Supported");
    }
  } catch (err) {
    console.error(err);
    res.send("Script File Not Found");
  }
})

app.post('/*', (req, res) => {
  res.send({
    "status": "error",
    "msg": 'Not Available',
    "method": req.method,
    "url": req.url
  });
})

app.get('/*', (req, res) => {
  res.send({
    "status": "error",
    "msg": 'Not Available',
    "method": req.method,
    "url": req.url
  });
})

console.log("\x1b[31m%s\x1b[0m", "Welcome Logiks NodeJS Bridge");

//Starting the Local server
server.listen(_CONFIG.LOCAL_PORT, 'localhost');
server.on('listening', function() {
  setTimeout(function() {
    console.log("\x1b[31m%s\x1b[0m", `Running on port ${server.address().port} at ${server.address().address}`);
  }, 500);
});

//Starting Public Socket Server


//Starting Local PCRON Service
if(_CONFIG.PCRON) {
  if(_CONFIG.PCRON===true) {
    _CONFIG.PCRON = "http://localhost/pcron";
    setInterval(async function () {
        axios
          .get(_CONFIG.PCRON)
          .then(function (response) {
            console.log("PCRON_LOG",  _CONFIG.PCRON, response.status);
          });
      }, _CONFIG.DEFAULT_PERIOD_SCHEDULING);

    console.log("PCRON Started", _CONFIG.PCRON);
  } else if(typeof _CONFIG.PCRON == "object") {
    _.each(_CONFIG.PCRON, function(tPeriod, lx) {
      
      setInterval(async function () {
        axios
          .get(lx)
          .then(function (response) {
            console.log("PCRON_LOG", lx, response.status);
          });
      }, tPeriod);

      console.log("PCRON Started", lx, tPeriod);
    });
  } else if(typeof _CONFIG.PCRON == "string") {
    setInterval(async function () {
        axios
          .get(_CONFIG.PCRON)
          .then(function (response) {
            console.log("PCRON_LOG",  _CONFIG.PCRON, response.status);
          });
      }, _CONFIG.DEFAULT_PERIOD_SCHEDULING);

    console.log("PCRON Started", _CONFIG.PCRON);
  } else {
    console.log("PCRON Type Not Supported");
  }
} else {
  console.log("PCRON Service Not Configured");
}

//Starting Local Schedulling Utility Service
if(_CONFIG.SCHEDULER) {
  _.each(_CONFIG.SCHEDULER, function(tPeriod, lx) {
    setInterval(async function () {
        axios
          .get(lx)
          .then(function (response) {
            console.log("SCHEDULER_LOG", lx, response.status);
          });
      }, tPeriod);

    console.log("SCHEDULER Service Started", lx, tPeriod);
  });
}



//Other Future Items

//Connect to Local MYSQL
//Connect to Local Cache
//Messaging Libary
//Start Auth Library