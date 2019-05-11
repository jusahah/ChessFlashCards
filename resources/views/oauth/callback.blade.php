<html>
<head>
  <meta charset="utf-8">
  <title>{{ config('app.name') }}</title>
  <script>
    // Parent window is our Vue app that initiated social login flow.
    // It is waiting for token so pass the token to it and close this window.
    window.opener.postMessage({ token: "{{ $token }}" }, "*")
    window.close()
  </script>
</head>
<body>
</body>
</html>