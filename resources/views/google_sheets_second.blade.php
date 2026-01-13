<!DOCTYPE html>
<html>
<head>
    <title>Google Sheet 2 Data</title>
</head>
<body>
    <h1>Google Sheet 2 Data</h1>
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
