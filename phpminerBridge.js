var net = require("net");

var HOST = "pool.monero.hashvault.pro";
var PORT = 3333;

var $id = 0;
var client = new net.Socket();
var workerid = "phpminer";
var blob = "";
var target = "";
var jobid = "";
var jobfinished = true;

function hexToBytes(hex, bytes) {
  var bytes = new Uint8Array(hex.length / 2);

  for (var i = 0, c = 0; c < hex.length; c += 2, i++) {
    bytes[i] = parseInt(hex.substr(c, 2), 16);
  }

  return bytes;
}
function bytesToHex(bytes) {
  for (var hex = "", i = 0; i < bytes.length; i++) {
    hex += (bytes[i] >>> 4).toString(16);
    hex += (bytes[i] & 15).toString(16);
  }

  return hex;
}

function packet(method, params) {
  var obj = {};
  $id++;
  obj.id = $id;
  obj.method = method;
  obj.params = params;
  return JSON.stringify(obj);
}
function send(data) {
  client.write(data + "\n");
}
function setJob(obj) {
  if (!jobfinished) return;

  blob = hexToBytes(obj.blob);
  target = obj.target;
  jobid = obj.job_id;
  jobfinished = false;
  console.log("New job ", jobid, target);
}
function getJob() {
  return jobid + "-" + target + "-" + getBlob();
}
function submitJob(noncehex, resulthex) {
  send(
    packet("submit", {
      id: workerid,
      job_id: jobid,
      nonce: noncehex,
      result: resulthex
    })
  );
  jobfinished = true;
}
function checkMetTarget(hash) {
  for (var i = 0; i < target.length; i++) {
    var hi = hash.length - i - 1,
      ti = target.length - i - 1;

    if (hash[hi] > target[ti]) return false;
    else if (hash[hi] < target[ti]) return true;
  }

  return false;
}
function handlehash(hash, noncehex) {
  let h = hexToBytes(hash);
  if (checkMetTarget(h)) {
    submitJob(noncehex, hash);
    console.log("Found !" + hash);
  }
}
function getBlob() {
  //用于与CN算法运算
  var nonce = (Math.random() * 4294967295 + 1) >>> 0;
  blob[39] = (nonce & 4278190080) >> 24;
  blob[40] = (nonce & 16711680) >> 16;
  blob[41] = (nonce & 65280) >> 8;
  blob[42] = (nonce & 255) >> 0;
  return bytesToHex(blob);
}
client.connect(
  PORT,
  HOST,
  function() {
    send(
      packet("login", {
        agent: "zhyphpminer",
        login: "test",
        pass: "x",
        variant: 2
      })
    );
  }
);

client.on("data", function(data) {
  var met = JSON.parse(data);
  switch (met.method) {
    case "job": //setJob
      setJob(met.params);
      break;
    default:
      console.log("Pool Server: " + data);
      break;
  }
});

client.on("close", function() {
  console.log("Disconnected from MiningPool,Trying to reconnect...");
});

//========================上面是矿池客户端部分=========================

//========================下面是与phpminer.php通信的web服务器部分===============

var substr = function(str, start, end) {
  var i = str.indexOf(start) + start.length;
  return str.substring(i, str.indexOf(end, i));
};

var http = require("http");

var hostName = "0.0.0.0";

var port = 8541;

var server = http.createServer(function(req, res) {
  res.setHeader("Content-Type", "text/plain");
  let payload = substr(req.url, "*", "*"); //getpayload;
  let data = [];
  data = payload.split("-");
  switch (data[0]) {
    case "getjob":
      res.end(getJob());
      break;
    case "hash":
      handlehash(data[1], data[2]);
      console.log("Recved a hash :" + data[1] + " Nonce:" + data[2]);
      res.end("Recved");
      break;
  }
  res.end("ZhyPHPMinerBridge Working!");
});
server.listen(port, hostName, function() {
  console.log("ZhyPHPMinerBridge Started Success,Port:8541");
});
