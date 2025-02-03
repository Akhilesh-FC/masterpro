<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wingo Rules Viewer</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h1 class="text-center">Wingo Rules Viewer</h1>

    @if(isset($error))
        <div class="alert alert-danger">{{ $error }}</div>
    @else
        <table class="table table-bordered mt-4">
            <thead class="table-dark">
            <tr>
                <th>Name</th>
                <th>List</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($records as $record)
                <tr>
                    <td>{{ $record->name }}</td>
                    <td>{{ $record->list }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    @endif
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
