# Job log for factories

### Simple tracking of the job done in factory per employee, order, operation ###

----------

There are several screens/forms:

1. List of employees - first and last name, nickname, team, flag for active/fired
2. List of work operations - name, cost, flag for active/obsolete
3. List of orders - order ID, project name, country, flag for active/finished
4. Log of the job done - who has done a given amount and type of operations regarding some order/project
5. Reports - employee worksheets, job done per employee/operation/order, payroll for salary (optionally excluding some operations)

Unfortunately there is no web interface for creating accounts - so you will have to manually edit table USER in database.
