# Sched Coding Challenge:

### Goal

You will be creating an event page (schedule) that has a list of sessions (schedule items). Each of the sessions has speakers who will be presenting. Your job is to combine the speaker and session data, sort the sessions alphabetically based on the speaker's last name, and output the list in any way that you desire (console, HTML, etc). Include at least the session name, the speaker(s) name, and session start/end time.
The goal is to simulate accessing data from a database, serializing the information, manipulating the data, and outputting the data in any format.

### Data

The input data is provided in 3 SQL dump files: `session.sql`, `role.sql` and `user.sql`. The session data, including session unique IDs is contained in the `session.sql` file. The user profiles, including user unique IDs is in the `user.sql` file. Finally, the `role.sql` file contains session-to-user mappings. MySQL is the preferred RDBMS for this project. The data is pretty simple, but take a look to familiarize yourself with it.

### Gotchas

Sessions can be active (published), or inactive (unpublished). Filter the non-active sessions out from the final data set. Note also, that speakers are not the only role a user can have. Finally, speakers may not all have last names. Maybe there are others?

### Example output data

```
Session Foo - Mar 1st 2018 10:50am-12:30pm
  - Adam Adams
  - John Doe

Session Bar - Mar 1st 2018 12:30pm-1:30pm
  - Buggy Bugowsky

Session Baz - Feb 28th 2018 12:30pm-1:30pm
  - Sandra Clinton
```

### Requirements

All tools that you would use in your day to day job are fair game. Google is your friend. Take three simulated Database dumps (Sessions, Speakers, Join table) which are stored in the repo as SQL files. Combine the data and sort these sessions by the speaker's last name. Your output does not need to follow the example given above, verbatim. It's just an example. 

While you should be able to complete this challenge in roughly 2-3 hours, do not feel compelled to timebox yourself. Feel free to add your own flair and make the code your own. If needed, make your best assumptions and explain how/why.


### Steps Taken:
1. Create a local database using `setup/create-db.sql`.
2. Run `data/` sql scripts while connected to the sched db to load sample data.
3. Create a view active_session using `setup/create-active_session-view.sql`.
4. Create an index on session/active to support the view using `setup/create-session_active-index.sql`.
  a. This resulted in an error `Invalid default value for 'session_start'`.
  b. Verified that no data in the table uses the current default (all dates are in 2019).
  c. Added a statement to change the default to '1900-01-01 00:00:00' - a reasonable date not likely to be problematic.
5. Run `setup/add-user-sort_last_name-column.sql` to add a new calculated sort name to the user table.
6. Set up a query to get the active session data joined through roles to users filtered on role type speaker. `queries/active_sessions_by_speaker_sort_name.sql`.
7. Set up a `.env` file for environmental parameters.
8. Set up the script which will:
  a. read environment from .env file
  a. connect to the database
  b. execute the query
  c. collate the data as there can be more than one speaker per session
  d. output the formatted data
9. Run `python reports/sessions_by_last_name.py` from the root directory of the project.


### Notes and Assumptions:
1. There is no specific field designated as a last name for the users, so it is likely to have bugs in the mechanism used to get a user's last name. This should prompt a discussion about adding `first name` and `last name` fields, splitting the data in the database and redefining the existing `name` field as a *GENERATED ALWAYS* field.
2. Given that there is a fundamental split in the session data between active and inactive sessions, it would make sense to add an index on this field. I have added a view that takes advantage of this split to simplify query joins.
3. The decision to update the DB to fix the default of '0000-00-00 00:00:00' begs for a product discussion about sesion dates, to ensure that there is no issue with changing this default. Looking at the DB description it looks like the session active status is 'Y' which functionally does not make sense, since a session will likely not have been finished being edited or finalized when initially created. This default should be changed.
4. `SELECT usertype, sessionid, count(*) FROM role GROUP BY usertype, sessionid;` makes it clear that a session can have more than one speaker begging the question of, when sorting, which user should it be sorted by? Looking at the example result above, which does in fact show one session with more than one speaker, the speakers appear to be alphabetical order within the listing, and it is sorted by the earliest last name.
5. It is unspecified as to  what to do in a case where there is no last name, or where the last name is fuzzy (e.g.: '1' or '(Ryan)'). For this exercise, I will regex away any numbers and puctuation marks for the sort by column and create a calculated sorting column. 
6. Assumption - the amount of data being encountered is small enough not to require using anything to deal with pagination.
7. I have not done anything to address HTML in the descriptions.
8. Python v3.10.9 was used for this code.
9. `87ac84437c9b76c66e3decb94c0f11a4 Robots` has an 'Active' designation of 'A' - it is unclear if this is intended to be treated as 'Active' or some other designation. I am including it in my output.
10. `22db6b4769ed316d92f0b20d8e9ab6d3 Graduation` has no speaker. It has a designated 'artist' associated with it.  Given that the sessions are being listed order by speaker, and not by person in general, the 'Graduation' session would have been excluded. I adjusted my query to not filter by 'speaker', included the role in the output, and then filtered the speakers only when collating the rows before outputting.


### Sample Output:

```
Sessions by Last Name
=====================

Robotics Banners (Aerospace Robotics [SILVER], Robotics [GOLD])
----------------------------------------------------------------
  01/25/2019, 10:00 - 11:00

• Create banners to represent the attributes of your <a href
  ="https://schedspacecamp2015.sched.com/editor/\\\\&quot;ht
  tps:/en.wikipedia.org/wiki/Robotics\\\\&quot;""
  target=""\\\\&quot;_self\\\\&quot;"">robotics</a> group."

  Speakers:
    Bill (bill@headnix.com)
    Lego
    Checkin Test (racheldrudi+checkin@gmail.com)
```

### PHP Version

*Assuming executing in linux or macOs:*
1. From a terminal console run `which php`.
2. Replace the first line with '#!' followed by the value returned in step one.
3. From the rood firectory of this project (the folder in which this file resides), run `./reports/sessions_by_last_name.php` in the terminal to execute the program.