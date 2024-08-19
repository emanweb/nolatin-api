<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Enter a JSON</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@3.3.7/dist/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">


</head>
<body style="margin: 1rem;">
<div class="container">
    <header>
        <h1>Manual Data Entry</h1>
        <p><a href="list.php">Back to list</a></p>
    </header>
    <main style="margin: 2rem;">
        <form method="post" action="save.php" title="JSON Input Form">
          <input type="hidden" name="localform" value="true">
            <div class="row justify-content-center">
                <div class="col-6">
            <section class="form-group">
                <label class="control-label" for="friendly_name">Friendly Name:</label>
                <input class="form-control" type="text" name="friendly_name" id="friendly_name" placeholder="Enter a friendly name for the JSON content">
            </section>
            <section class="form-group">
                <label class="control-label" for="json_content">JSON Content:</label>
                <textarea class="form-control" name="json_content" id="json_content" placeholder="Enter the JSON content"></textarea>
            </section>
            <section class="form-group">
                <label class="control-label" for="emailaddress">Email Address:</label>
                <input class="form-control" type="email" name="emailaddress" id="emailaddress" placeholder="Enter your email address">
            </section>
            <input type="submit" value="Submit" class="btn btn-primary">
            </div>
        </div>
        </form>
    </main>
    <footer style="margin-top: 2rem; background-color: #eee;">
        <p>&copy; 2023. All rights reserved.</p>
    </footer>
    </div>
</body>
</html>