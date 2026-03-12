var firebaseConfig = {
  apiKey: "AIzaSyBtt_HQzC55eVnmsIyGIXYtK5rMXm7Eh2Q",
  projectId: "rticket-812d3",
  messagingSenderId: "1004822054480",
  appId: "1:1004822054480:web:ad43f9bae03c48bf6b7355"
};

firebase.initializeApp(firebaseConfig);

const messaging = firebase.messaging();

async function initFCM(reg) {
  console.log("Init FCM clicked");

  const permission = await Notification.requestPermission();
  console.log("Permission:", permission);

  if (permission !== "granted") {
    alert("Notifikasi ditolak");
    return;
  }

  const token = await messaging.getToken({
    vapidKey: "BEq07VU6lTu1ke2KClKQlTClCY_iNFW-W9iq7yftdhXsNLi6_21BMNo4owa8HLBlvVtxqpq4WagH5feub9Ep1Hs",
    serviceWorkerRegistration: reg
  });

  console.log("🔥 FCM TOKEN:", token);

  await fetch("/save_fcm_token.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ token })
  });
  const json = await res.json();
    console.log("SAVE TOKEN RESPONSE:", json);
}
