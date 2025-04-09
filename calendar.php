<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    file_put_contents('events.json', json_encode($_POST['events']));
    exit;
}
$events = file_exists('events.json') ? file_get_contents('events.json') : '[]';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simple Calendar</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/5.11.3/main.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/5.11.3/main.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        #calendar { max-width: 900px; margin: auto; }
    </style>
</head>
<body>

<h2>Event Calendar</h2>
<div id="calendar"></div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');
    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        editable: true,
        selectable: true,
        events: <?php echo $events; ?>,
        select: function(info) {
            var title = prompt("Enter Event Title:");
            if (title) {
                var newEvent = { id: Date.now(), title: title, start: info.startStr, end: info.endStr };
                calendar.addEvent(newEvent);
                saveEvents(calendar.getEvents());
            }
        },
        eventDrop: function(info) {
            saveEvents(calendar.getEvents());
        },
        eventClick: function(info) {
            if (confirm("Delete this event?")) {
                info.event.remove();
                saveEvents(calendar.getEvents());
            }
        }
    });
    calendar.render();

    function saveEvents(events) {
        var eventsArray = events.map(e => ({ id: e.id, title: e.title, start: e.startStr, end: e.endStr }));
        $.post("calendar.php", { events: eventsArray });
    }
});
</script>

</body>
</html>
