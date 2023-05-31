#!/opt/homebrew/bin/php
<?php

function get_sessions_by_last_name($dbh) {
    $query = <<<END
    SELECT 
        u.sort_last_name AS user_sort_last_name,
        s.id AS session_id,
        s.name AS session_name,
        s.session_start,
        s.session_end,
        s.session_type,
        s.session_subtype,
        s.description,
        r.usertype,
        u.name AS user_name,
        u.email AS user_email,
        u.url AS user_url,
        u.avatar AS user_avatar
    FROM 
        active_session s
    LEFT JOIN
        role r ON r.sessionid = s.id
    LEFT JOIN
        user u ON u.id = r.userid
    ORDER BY 
        u.sort_last_name, 
        s.session_start
    END;
    try {
        $cursor = $dbh->query($query);
        $data = $cursor->fetchAll(PDO::FETCH_ASSOC);
        // print_r($data);
        return $data;
    } catch (PDOException $exception) {
        print($exception->getMessage());
    }
}

function collate_sessions_by_id($data) {
    $sessions = array();
    foreach ($data as $row) {
        $session_id = $row['session_id'];
        if (!array_key_exists($session_id, $sessions)) {
            $sessions[$session_id] = array(
                'session_id' => $session_id,
                'session_name' => $row['session_name'],
                'session_start' => $row['session_start'],
                'session_end' => $row['session_end'],
                'session_type' => $row['session_type'],
                'session_subtype' => $row['session_subtype'],
                'description' => $row['description'],
                'speakers' => array()
            );
        }
        if ($row['usertype'] == 'speaker') {
          $sessions[$session_id]['speakers'][] = array(
            'user_sort_last_name' => $row['user_sort_last_name'],
            'user_name' => $row['user_name'],
            'user_email' => $row['user_email'],
            'user_url' => $row['user_url'],
            'user_avatar' => $row['user_avatar'],
            'usertype' => $row['usertype']
          );
        }
    }
    // print_r($sessions);
    return $sessions;
}

function print_sessions($sessions, $report_title) {
    print("{$report_title}\n");
    $underline = str_repeat('=', mb_strlen($report_title));
    print("{$underline}\n\n");
    foreach ($sessions as $session) {
        $session_id = substr($session['session_id'], 0, 3);
        print("{$session['session_name']} ({$session['session_type']}) #{$session_id}\n");
        $length = mb_strlen($session['session_name']) + mb_strlen($session['session_type']) + 4;
        $underline = str_repeat('-', $length);
        print("{$underline}\n");

        print("{$session['session_type']} - {$session['session_subtype']}\n");
        print("{$session['session_start']} - {$session['session_end']}\n\n");

        if (strlen($session['description'] > 0)) {
          $description = wordwrap("• " . $session['description'], 60, "\n  ");
          print("{$description}\n\n");
        } 
        foreach ($session['speakers'] as $speaker) {
            $email = $speaker['user_email'] ? " <{$speaker['user_email']}>" : '';
            print("    {$speaker['user_name']} $email\n");
        }
        print("\n");
    }
}

function main() {
  $env = parse_ini_file('.env');
  // print_r($env);
  $report_title = "Sessions by Last Name";
  try {
    $dbh = new PDO("mysql:host={$env['DB_HOST']};dbname={$env['DB_DBAS']}", $env['DB_USER'], $env['DB_PASS']);
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // print_r($dbh);
    $data = get_sessions_by_last_name($dbh);
    $sessions = collate_sessions_by_id($data);

    print_sessions($sessions, $report_title);       

    $dbh = null;
  } catch (PDOException $e) {
    print "Error!: " . $e->getMessage() . "<br/>";
    die();
  }
}

main();