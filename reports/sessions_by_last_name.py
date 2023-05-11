import mysql.connector
import os
import textwrap

from dotenv import load_dotenv

def get_sessions_by_last_name(cnx):
    query = """SELECT 
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
    """
    try:
        cursor = cnx.cursor(dictionary=True)
        cursor.execute(query)
        data = cursor.fetchall()
        return data
    except mysql.connector.Error as err:
        print(err)

def collate_sessions_by_id(data):
    sessions = {}
    for row in data:
        session_id = row['session_id']
        if session_id not in sessions:
            sessions[session_id] = {
                'session_name': row['session_name'],
                'session_start': row['session_start'],
                'session_end': row['session_end'],
                'session_type': row['session_type'],
                'session_subtype': row['session_subtype'],
                'description': row['description'],
                'speakers': []
            }
        if (row['usertype'] == 'speaker'):
            sessions[session_id]['speakers'].append({
                'user_name': row['user_name'],
                'user_email': row['user_email'],
                'user_url': row['user_url'],
                'user_avatar': row['user_avatar']
            })
    return sessions

def print_sessions(sessions, report_title):
    print(report_title)
    print("=" * len(report_title))
    print()
    for session_id, session in sessions.items():
        print(f"{session['session_name']} ({session['session_type']}) #{session_id[0:3]}]}}")
        print("-" * (len(session['session_name'] + session['session_type']) + 4))
        print(f"  {session['session_start'].strftime('%m/%d/%Y, %H:%M')} - {session['session_end'].strftime('%H:%M')}")
        print()
        print(textwrap.fill(session['description'], width=60, initial_indent="• ", subsequent_indent="  "))
        print()
        if (len(session['speakers']) > 0):
            print("  Speakers:")
            for speaker in session['speakers']:
                email = speaker['user_email'] and f" ({speaker['user_email']})" or ""
                print(f"    {speaker['user_name']}{email}")
        print()
        print()

    print(len(sessions.items()) and f"Total Sessions: {len(sessions.items())}" or "No sessions found.")



def main():
    load_dotenv()
    report_title = "Sessions by Last Name"
    try:
        cnx = mysql.connector.connect(
            user=os.getenv('DB_USER'), 
            password=os.getenv('DB_PASS'),
            host=os.getenv('DB_HOST'),
            database=os.getenv('DB_DBAS'))
        
        data = get_sessions_by_last_name(cnx)
        sessions = collate_sessions_by_id(data)

        print_sessions(sessions, report_title)        
    except mysql.connector.Error as err:
        print(err)

main()