<!DOCTYPE html>
<html>
<head>
    <title>Google Sheet Data</title>
</head>
<body>
    <h1>Google Sheet Data</h1>
    <table border="1" cellpadding="5">
        @foreach($values as $row)
            <tr>
                @foreach($row as $cell)
                    <td>{{ $cell }}</td>
                @endforeach
            </tr>
        @endforeach
    </table>
</body>
</html>
