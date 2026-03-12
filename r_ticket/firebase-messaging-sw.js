importScripts('https://www.gstatic.com/firebasejs/9.23.0/firebase-app-compat.js');
importScripts('https://www.gstatic.com/firebasejs/9.23.0/firebase-messaging-compat.js');

firebase.initializeApp({
  apiKey: "AIzaSyBtt_HQzC55eVnmsIyGIXYtK5rMXm7Eh2Q",
  projectId: "rticket-812d3",
  messagingSenderId: "1004822054480",
  appId: "1:1004822054480:web:ad43f9bae03c48bf6b7355"
});

const messaging = firebase.messaging();

/* ===============================
   DATA ONLY HANDLER
================================ */
messaging.onBackgroundMessage(payload => {
  console.log('[SW] DATA PUSH:', payload);

  const title = payload.data.title;
  const options = {
    body: payload.data.body,
    icon: 'https://amynt.my.id/r_ticket/logo.png',
    badge: 'https://amynt.my.id/r_ticket/logo.png',
    data: {
      url: payload.data.url
    }
  };

  self.registration.showNotification(title, options);
});

/* ===============================
   CLICK
================================ */
self.addEventListener('notificationclick', event => {
  event.notification.close();
  event.waitUntil(
    clients.openWindow(event.notification.data.url)
  );
});
