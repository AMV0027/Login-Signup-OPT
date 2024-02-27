// send_notification.php
<?php
// Code to fetch subscription information from your database or wherever it's stored
// sw.js
// sw.js
self.addEventListener('push', function(event) {
    const options = {
        body: event.data.text(),
    };
    event.waitUntil(self.registration.showNotification('Title', options));
});



// Convert the subscription to JSON
$subscription_json = json_encode($subscription);

// Send notification to the subscription
$headers = array(
    'Authorization: key=YOUR_SERVER_KEY',
    'Content-Type: application/json'
);

$data = array(
    'title' => 'Notification Title',
    'message' => 'Notification Message',
);

$payload = array(
    'registration_ids' => array($subscription_json),
    'data' => $data
);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
$result = curl_exec($ch);
curl_close($ch);

// Handle the result as needed
?>
