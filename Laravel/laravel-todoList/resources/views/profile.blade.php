<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile</title>
    <link rel="stylesheet" href="css/profile.css">
</head>
<body >
   <div class="profile">
  <div class="box">
    <span class="title">User Information</span>
    <div>
      <strong>{{ $user->name }}</strong>
      <p>{{ $user->email }}</p>
    </div>
            <a href="{{ route('tasks.index') }}" class="btn">Go to Tasks</a>

  </div>
</div>
</body>
</html>


