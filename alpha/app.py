from flask import Flask, render_template, request, redirect, url_for, flash, session, jsonify
from werkzeug.security import generate_password_hash, check_password_hash
from flask_mail import Mail, Message
from flask_login import login_required, current_user
from datetime import datetime
from flask_sqlalchemy import SQLAlchemy
from functools import wraps
import mysql.connector
import os
import secrets
import pyodbc  # For SQL Server
# If using MySQL, uncomment the line below
# import mysql.connector  # For MySQL
import random
import string

app = Flask(__name__)
app.secret_key = '23468'  # Use a random secret key for better security

# Configuration for SQLite (for now)
app.config['SQLALCHEMY_DATABASE_URI'] = 'sqlite:///Alphamechanic.db'
db = SQLAlchemy(app)

# Configure Flask-Mail using environment variables for sensitive data
app.config['MAIL_SERVER'] = 'smtp.office365.com'
app.config['MAIL_PORT'] = 587
app.config['MAIL_USE_TLS'] = True
app.config['MAIL_USERNAME'] = '221734597@mywsu.ac.za'  # Use environment variable
app.config['MAIL_PASSWORD'] = 'Kg$ug3LO%1'  # Use environment variable
app.config['MAIL_DEFAULT_SENDER'] = '221734597@mywsu.ac.za'

mail = Mail(app)

# Function to establish database connection
# For SQL Server (using pyodbc)
# def get_db_connection():
#     server = os.getenv('DB_SERVER', 'DIGITALSERVICEI\\SQLEXPRESS')  # Configurable through env
#     database = os.getenv('DB_NAME', 'Alphamechanic')  # Configurable through env
#     username = os.getenv('DB_USERNAME', 'ALPHAMECH')  # Configurable through env
#     password = os.getenv('DB_PASSWORD', 'Kgau123@M')  # Configurable through env
#     driver = '{ODBC Driver 17 for SQL Server}'

#     try:
#         conn = pyodbc.connect(
#             f'DRIVER={driver};'
#             f'SERVER={server};'
#             f'DATABASE={database};'
#             f'UID={username};'
#             f'PWD={password};'
#             f'Trusted_Connection=no;'
#         )
#         return conn
#     except pyodbc.Error as e:
#         print("Error while connecting to SQL Server:", e)
#         return None

# Uncomment this function if using MySQL (with mysql-connector or pymysql)
def get_db_connection():
    try:
        conn = mysql.connector.connect(
            host='localhost',  # XAMPP MySQL server
            user='root',       # MySQL default user (change if needed)
            password='',        # Default MySQL root password is empty
            database='alphamec'
        )
        return conn
    except mysql.connector.Error as e:
        print("Error while connecting to MySQL:", e)
        return None

@app.route('/')
def home():
    return render_template('index.html')

@app.route('/login', methods=['GET', 'POST'])
def login_page():
    if request.method == 'POST':
        email = request.form.get('email')
        password = request.form.get('password')

        # Establish database connection
        conn = get_db_connection()
        if conn is None:
            flash("Failed to connect to the database.", "danger")
            return redirect(url_for('login_page'))

        cursor = conn.cursor()
        cursor.execute('SELECT PersonID, fullname, Password FROM Persons WHERE Email = %s', (email,))

        user = cursor.fetchone()

        # Close the cursor and connection
        cursor.close()
        conn.close()

        if user and password == user[2]:
            session['user_id'] = user[0]   # Store user ID in session
            session['user_fullname'] = user[1]  # Store user's full name in session
            flash("Login successful!", "success")
            return redirect(url_for('dashboard'))
        else:
            flash("Invalid credentials. Please try again.", "danger")

    return render_template('login.html')



# CUSTOMERS
@app.route('/Customer-dashboard')
def dashboard():
    if 'user_fullname' in session:
        return render_template('New_Customer/index.html', fullname=session['user_fullname'], user_id=session['user_id'])
    else:
        flash('Please log in to access your account.', 'warning')
        return redirect(url_for('login_page'))

def login_required(f):
    """Decorator to require login for certain routes."""
    @wraps(f)
    def decorated_function(*args, **kwargs):
        if 'user_fullname' not in session:
            flash('Please log in to access this page.', 'warning')
            return redirect(url_for('login_page'))
        return f(*args, **kwargs)
    return decorated_function

@app.route('/catalog')
def catalog():
    return render_template('catalog.html')

@app.route('/Catalogue')
@login_required
def catalogue():
    if 'user_fullname' in session:
        return render_template('New_Customer/catalogue.html', fullname=session['user_fullname'], user_id=session['user_id'])
    else:
        flash('Please log in to access your account.', 'warning')
        return redirect(url_for('login_page'))

@app.route('/returns')
@login_required
def returns():
    if 'user_fullname' in session:
        return render_template('New_Customer/returns.html', fullname=session['user_fullname'], user_id=session['user_id'])
    else:
        flash('Please log in to access your account.', 'warning')
        return redirect(url_for('login_page'))

@app.route('/address-book')
@login_required
def address():
    if 'user_fullname' in session:
        return render_template('New_Customer/address_book.html', fullname=session['user_fullname'], user_id=session['user_id'])
    else:
        flash('Please log in to access your account.', 'warning')
        return redirect(url_for('login_page'))
@app.route('/add_address', methods=['POST'])
@login_required
def add_address():
    if request.method == 'POST':
        # Get form data
        street = request.form.get('street')
        surburb = request.form.get('suburb')
        town = request.form.get('town')
        province = request.form.get('province')
        postalcode = request.form.get('postalcode')
        # longitude = request.form.get('longitude')

        # Validate the form data
        
    
        conn = get_db_connection()
        cursor = conn.cursor()
        user_id = session['user_id']
        print(2)
        cursor.execute('INSERT INTO Address (Street, Suburb, Town, Province, PostalCode,PersonID) VALUES ( %s,%s, %s, %s, %s, %s)',
               (street, surburb, town, province, postalcode, user_id))
  # Replace 1 with actual person_id if needed
        conn.commit()
        cursor.close()
        conn.close()

        flash('Address added successfully!', 'success')
        return redirect(url_for('address_book'))  # Redirect to address book after adding
       
    # If it's a GET request, render the add address form
    return render_template('New_Customer/address_book.html')  # Make sure this file is in your templates folder
@app.route('/delete_address/<int:address_id>', methods=['POST'])
@login_required
def delete_address(address_id):
    conn = get_db_connection()
    cursor = conn.cursor()
    user_id = session['user_id']
    print(user_id)
    # Delete the address based on the provided address_id
    cursor.execute('DELETE FROM Address WHERE AddressID = %s', (address_id,))
   
    conn.commit()
    cursor.close()
    conn.close()

    flash('Address deleted successfully!', 'success')
    return redirect(url_for('address_book'))  # Redirect to address book after deletion

@app.route('/address_book')
@login_required
def address_book():
    # Fetch addresses from the database to display them in the address book
    conn = get_db_connection()
    cursor = conn.cursor()
    user_id = session['user_id']
    cursor.execute('SELECT * FROM Address WHERE PersonID = %s', (user_id,))
  # Replace with your actual table name
    addresses = cursor.fetchall()
    cursor.close()
    conn.close()

    return render_template('New_Customer/address_book.html', addresses=addresses)

@app.route('/track-orders')
@login_required
def track_orders():
    if 'user_fullname' in session:
        return render_template('New_Customer/track_orders.html', fullname=session['user_fullname'], user_id=session['user_id'])
    else:
        flash('Please log in to access your account.', 'warning')
        return redirect(url_for('login_page'))
@app.route('/track_orders', methods=['POST'])
@login_required  # Ensures users must be logged in to track their orders.
def track_order():
    if 'user_fullname' in session:
        if request.method == 'POST':
            OrderNumber = request.form.get('orderNumber')
            if not OrderNumber or len(OrderNumber) < 5:  # Example validation check
                flash('Invalid order number. Please enter a valid order number.', 'warning')
                return redirect(url_for('track_orders'))
            # Query the order by OrderNumber
            conn = get_db_connection()
            cursor = conn.cursor()
            
            # Execute the query with a parameterized statement
            cursor.execute('SELECT OrderNumber, Status FROM Orders WHERE OrderNumber = %s', (OrderNumber,))
            orderStatus = cursor.fetchall()
            cursor.close()
            conn.close()

            # Check if any orders were returned
            if not orderStatus:  # Changed from if not orderStatus[0] to if not orderStatus
                flash('Order not found. Please check your order number.', 'warning')
                return redirect(url_for('track_orders'))

            return render_template('New_Customer/track_orders.html', orderStatus=orderStatus, fullname=session['user_fullname'])
        
        # If it's a GET request, render the tracking form
        return render_template('New_Customer/track_orders.html', fullname=session['user_fullname'])
    
    else:
        flash('Please log in to access your account.', 'warning')
        return redirect(url_for('login_page'))
@app.route('/track_ord')
def track_or():    
    return render_template('track_orders.html')

# @app.route('/track_ord', methods=['POST'])
# def track_ord():

#     if request.method == 'POST':
#         OrderNumber = request.form.get('orderNumber')
#         if not OrderNumber or len(OrderNumber) < 5:  # Example validation check
#             flash('Invalid order number. Please enter a valid order number.', 'warning')
#             return redirect(url_for('track_orders'))
#         # Query the order by OrderNumber
#         conn = get_db_connection()
#         cursor = conn.cursor()
        
#         # Execute the query with a parameterized statement
#         cursor.execute('SELECT OrderNumber, Status FROM Orders WHERE OrderNumber = %s', (OrderNumber,))
#         orderStatus = cursor.fetchall()
#         cursor.close()
#         conn.close()

#         # Check if any orders were returned
#         if not orderStatus:  # Changed from if not orderStatus[0] to if not orderStatus
#             flash('Order not found. Please check your order number.', 'warning')
#             return redirect(url_for('track_ord'))

#         return render_template('track_orders.html', orderStatus=orderStatus)
    
#     # If it's a GET request, render the tracking form
#     return render_template('New_Customer/track_orders.html', fullname=session['user_fullname'])
  

@app.route('/edit-profile/<int:user_id>')
@login_required
def edit_profile(user_id):
    return render_template('edit_profile.html', user_id=user_id)

@app.route('/cart')
@login_required
def cart():
    if 'user_fullname' in session:
        return render_template('New_Customer/cart.html', fullname=session['user_fullname'], user_id=session['user_id'])
    else:
        flash('Please log in to access your account.', 'warning')
        return redirect(url_for('login_page'))


@app.route('/update-quantity', methods=['POST'])
def update_quantity():
    data = request.json
    product_id = data.get('product_id')
    selected_quantity = data.get('quantity')


    conn = get_db_connection()
    cursor = conn.cursor()
    # Reduce quantity in the database based on the product ID
    cursor.execute('UPDATE Product SET Quantity = Quantity - ? WHERE ProductID = ?', (selected_quantity, product_id))

    conn.commit()
    conn.close()

    return jsonify({'status': 'success', 'message': 'Quantity updated successfully!'})

@app.route('/payment')
@login_required
def payment():
    if 'user_fullname' in session:
        return render_template('New_Customer/payment.html', fullname=session['user_fullname'], user_id=session['user_id'])
    else:
        flash('Please log in to access your account.', 'warning')
        return redirect(url_for('login_page'))

@app.route('/update_cart', methods=['POST'])
def update_cart():
    cart = request.json.get('cart')
    response = []

    if not cart:
        return jsonify({'error': 'No cart data provided.'}), 400

    conn = get_db_connection()
    if conn is None:
        return jsonify({'error': 'Failed to connect to the database.'}), 500

    cursor = conn.cursor()

    try:
        for item in cart:
            product_name = item.get('name')
            ordered_quantity = item.get('quantity')
            
            user_id = session.get('user_id')  # Ensure user_id is present

            if not product_name or not isinstance(ordered_quantity, int):
                response.append({'error': 'Invalid item data.', 'item': item})
                continue

            # Retrieve product by name
            cursor.execute('SELECT ProductID, Price, Quantity FROM Product WHERE ProductName = %s', (product_name,))
            product = cursor.fetchone()

            if product:
                product_id, price, available_quantity = product

                if available_quantity >= ordered_quantity:
                    # Update product quantity
                    cursor.execute('UPDATE Product SET Quantity = Quantity - %s WHERE ProductID = %s',
                                   (ordered_quantity, product_id))

                    # Retrieve CustomerID from Customer table based on PersonID (user_id)
                    cursor.execute('SELECT CustomerID FROM Customer WHERE PersonID = %s', (user_id,))
                    customer = cursor.fetchone()

                    if not customer:
                        return jsonify({'error': 'Customer not found for this user.'}), 400
                    
                    customer_id = customer[0]

                    # Insert the order into the Orders table
                    cursor.execute('INSERT INTO Orders (OrderNumber, Parts, Status, OrderDate, CustomerID, TotalAmount) '
                                   'VALUES (%s, %s, %s, NOW(), %s, %s)',
                                   (generate_order_number(), product_name, 'Pending', customer_id, price * ordered_quantity))

                    response.append({
                        'product_id': product_id,
                        'name': product_name,
                        'price': float(price),
                        'updated_quantity': available_quantity - ordered_quantity,
                        'status': 'success'
                    })
                else:
                    response.append({
                        'product_id': product_id,
                        'name': product_name,
                        'price': float(price),
                        'available_quantity': available_quantity,
                        'requested_quantity': ordered_quantity,
                        'error': 'Not enough stock'
                    })
            else:
                response.append({'error': f'Product "{product_name}" not found in the database.'})

        conn.commit()  # Commit all changes once, after processing the entire cart

    except Exception as e:
        print(f"Database error: {e}")  # This line will print the specific error in the console
        conn.rollback()  # Roll back any changes made during the transaction
        return jsonify({'error': f'Database error occurred while processing the items: {str(e)}'}), 500

    finally:
        cursor.close()
        conn.close()

    return jsonify(response), 200

def generate_order_number():
    """Generate a random order number."""
    return 'ORD' + ''.join(random.choices(string.ascii_uppercase + string.digits, k=6))
@app.route('/book-appointment')
@login_required
def book_appointment_page():
    if 'user_fullname' in session:
        return render_template('New_Customer/appointment.html', fullname=session['user_fullname'], user_id=session['user_id'])
    else:
        flash('Please log in to access your account.', 'warning')
        return redirect(url_for('login_page'))
    
@app.route('/book_appointments', methods=['POST'])
@login_required
def book_appointments():
    if request.method == 'POST':
        # Retrieve form data
        car_model = request.form.get('car_model').strip()
        service_type = request.form.get('service_type').strip()
        service_date = request.form.get('service_date').strip()
        comments = request.form.get('comments').strip()

        # Basic validation (ensure required fields are filled)
        if not car_model or not service_type or not service_date:
            flash("Please fill all required fields", "danger")
            return redirect(url_for('book_appointment_page'))

        # Parse the service date
        try:
            service_date = datetime.strptime(service_date, '%Y-%m-%d')  # Ensure it's a valid date format
            # Check if the date is in the future
            if service_date < datetime.now():
                flash("Service date cannot be in the past.", "danger")
                return redirect(url_for('book_appointment_page'))
        except ValueError:
            flash("Invalid date format. Please use YYYY-MM-DD.", "danger")
            return redirect(url_for('book_appointment_page'))

        # Ensure the user is logged in and has a valid session
        user_id = session.get('user_id')
        if not user_id:
            flash("You must be logged in to book an appointment.", "danger")
            return redirect(url_for('login'))

        # Establish database connection
        conn = get_db_connection()
        if conn is None:
            flash("Failed to connect to the database.", "danger")
            return redirect(url_for('book_appointment_page'))

        try:
            cursor = conn.cursor()

            # Insert appointment data into the database
            appointment_status = "Pending"
            cursor.execute(
                'INSERT INTO Appointment (CarModel, ServiceType, ServiceDate, Comments, AppointmentStatus) '
                'VALUES (%s, %s, %s, %s, %s)',
                (car_model, service_type, service_date, comments, appointment_status)
            )

            # Commit the transaction
            conn.commit()

            # Flash success message and redirect to a success or dashboard page
            flash("Your appointment has been successfully booked!", "success")
        
        except Exception as e:
            # Rollback in case of an error and show error message
            conn.rollback()
            flash(f"An error occurred while booking the appointment: {e}", "danger")
        
        finally:
            cursor.close()
            conn.close()

        return redirect(url_for('book_appointment_page'))

    # Render the form template for GET requests
    return render_template('New_Customer/view_appointments.html')

@app.route('/delete-appointment', methods=['POST'])
@login_required
def delete_appointment():
    user_id = session['user_id']
    appointment_id = request.form.get('appointment_id')  # Get the appointment ID from the form data

    if appointment_id is None:
        flash("No appointment ID provided.", "danger")
        return redirect(url_for('view_appointments'))  # Redirect to the view appointments page

    # Establish database connection
    conn = get_db_connection()
    if conn is None:
        flash("Failed to connect to the database.", "danger")
        return redirect(url_for('home'))

    cursor = conn.cursor()
    
    try:
         # Log the appointment ID for debugging
        print(f"Attempting to delete appointment ID: {appointment_id} for user ID: {user_id}")

        # Query to delete the specific appointment for the logged-in user
        cursor.execute('DELETE FROM Appointments WHERE Appointment_id = ? AND user_id = ?', (appointment_id, user_id))
        conn.commit()  # Commit the transaction
        # Check if any row was deleted
        if cursor.rowcount == 0:
            flash("No appointment found to delete.", "warning")
        else:
            flash("Appointment deleted successfully.", "success")
        
    except Exception as e:
        flash(f"An error occurred while deleting the appointment: {e}", "danger")
    finally:
        cursor.close()
        conn.close()

    # Redirect back to the view appointments page
    return redirect(url_for('view_appointments'))

@app.route('/view_appointments')
@login_required
def view_appointment_page():
    if 'user_fullname' in session:
        conn = get_db_connection()
        cursor = conn.cursor()
        user_id = session['user_id']
        cursor.execute('SELECT * FROM Appointments where user_id =?',(user_id))  # Replace with your actual table name
        appointments = cursor.fetchall()
        cursor.close()
        conn.close()

        return render_template('New_Customer/appointment.html',appointments=appointments, fullname=session['user_fullname'], user_id=session['user_id'])
    else:
        flash('Please log in to access your account.', 'warning')
        return redirect(url_for('login_page'))

@app.route('/view-appointments')
@login_required
def view_appointments():
    if 'user_fullname' in session:
        conn = get_db_connection()
        cursor = conn.cursor()

    # Get the user ID from the session
        user_id = session['user_id']

    # Fetch the CustomerID based on the PersonID (assumed)
        cursor.execute('SELECT CustomerID FROM Customer WHERE PersonID = %s', (user_id,))
        customer_id = cursor.fetchone()  # Use fetchone() since you're only expecting one result

        if customer_id:  # Ensure that customer_id is not None
            customer_id = customer_id[0]  # Extract the actual ID from the tuple

            # Fetch appointments based on the CustomerID
            cursor.execute('SELECT * FROM Appointment WHERE CustomerID = %s', (customer_id,))
            appointments = cursor.fetchall()
        else:
            appointments = []  # No appointments found for this user

            cursor.close()
            conn.close()

        return render_template('New_Customer/view_appointments.html',appointments=appointments, fullname=session['user_fullname'], user_id=session['user_id'])
    else:
        flash('Please log in to access your account.', 'warning')
        return redirect(url_for('login_page'))
@app.route('/view_orders')
def view_order():
    if 'user_fullname' in session:
        user_id = session['user_id']
        conn = get_db_connection()
        cursor = conn.cursor()
        cursor.execute('SELECT CustomerID FROM Customer WHERE PersonID = %s', (user_id,))
        customer = cursor.fetchone()
        customer_id = customer[0]
        cursor.execute('SELECT * FROM Orders WHERE CustomerID = %s', (customer_id,))
        orders = cursor.fetchall()
        cursor.close()
        conn.close()
        return render_template('New_Customer/view_orders.html',orders=orders, fullname=session['user_fullname'], user_id=session['user_id'])

    else:
        flash('Please log in to access your account.', 'warning')
        return redirect(url_for('login_page'))


@app.route('/logout')
def logout():
    session.clear()  # Clears the session
    flash('You have been logged out.', 'info')
    return redirect(url_for('login_page'))

@app.route('/forgot-password')
def forgot_password():

    return render_template('forgot_password.html')

@app.route('/forgot_password', methods=['GET', 'POST'])
def forgot_password_page():
    if request.method == 'POST':
        email = request.form.get('email')

        if not email:
            flash('Email is required.', 'danger')
            return redirect(url_for('forgot_password_page'))

        token = secrets.token_hex(16)  # Generate a random token
        conn = get_db_connection()
        cursor = conn.cursor()
        cursor.execute('SELECT * FROM Persons WHERE Email = ?', (email,))
        user = cursor.fetchone()

        if user:
            # Store token in the database
            cursor.execute('UPDATE Persons SET Token = ? WHERE Email = ?', (token, email))
            conn.commit()

            # Create a reset link
            reset_link = url_for('reset_password', token=token, _external=True)

            # Send reset email
            msg = Message('Password Reset Request', recipients=[email])
            msg.body = f'Click the link to reset your password: {reset_link}'
            mail.send(msg)

            flash('Password reset link has been sent to your email.', 'info')
        else:
            flash('Email not found.', 'danger')

        cursor.close()
        conn.close()
        return redirect(url_for('login_page'))

    return render_template('forgot_password.html')

@app.route('/reset_password/<token>', methods=['GET', 'POST'])
def reset_password(token):
    if request.method == 'POST':
        new_password = request.form.get('password')

        if not new_password:
            flash("Password is required.", "danger")
            return redirect(url_for('reset_password', token=token))

        conn = get_db_connection()
        cursor = conn.cursor()

        # Update password
        hashed_password = generate_password_hash(new_password)
        cursor.execute('UPDATE Persons SET Password = ?, Token = NULL WHERE Token = ?', (hashed_password, token))
        conn.commit()

        if cursor.rowcount:
            flash('Your password has been reset successfully.', 'success')
            return redirect(url_for('login_page'))
        else:
            flash('Invalid token or token has expired.', 'danger')

        cursor.close()
        conn.close()

    return render_template('reset_password.html', token=token)

@app.route('/register')
def register():
    return render_template('registration.html')
@app.route('/submit', methods=['POST'])
def register_page():
    if request.method == 'POST':
        # Get form data
        full_name = request.form.get('fullname')
        email = request.form.get('email')
        gender = request.form.get('gender')
        password = request.form.get('password')
        phone_number = request.form.get('phone')
        username = request.form.get('username')
        token = secrets.token_hex(16)  # Generate a random token

        # Check for None values and strip whitespace
        if any(field is None for field in [full_name, email, gender, password, phone_number, username]):
            flash("All fields are required.", "danger")
            return redirect(url_for('register_page'))  # Ensure that you return a response

        # Establish database connection
        conn = get_db_connection()
        if conn is None:
            flash("Failed to connect to the database.", "danger")
            return redirect(url_for('register_page'))  # Ensure that you return a response

        cursor = conn.cursor()

        # Check if the email already exists (Uncomment if needed)
        # cursor.execute('SELECT * FROM Persons WHERE Email = ?', (email,))
        # existing_user = cursor.fetchone()
        # if existing_user:
        #     flash("Email already registered. Please log in.", "danger")
        #     return redirect(url_for('login_page'))  # Ensure that you return a response

        PersonType = "CU"

        # Log the values before execution
        print(f"Inserting into Persons: {gender}, {username}, {email}, {password}, {phone_number}, {PersonType}, {full_name}, {token}")


        cursor.execute('INSERT INTO Persons (Gender, Username, Email, Password, PhoneNumber, PersonType, fullname, Token) VALUES (%s, %s, %s, %s, %s, %s, %s, %s)',
               (gender, username, email, password, phone_number, "CU", full_name, token))

            
            # Commit the transaction
        conn.commit()

        flash("Registration successful. Please log in.", "success")
        cursor.close()
        conn.close()
        return redirect(url_for('login_page'))  # Ensure you return a valid response

        

    # Return a response in case of a GET request (if needed, handle GET requests separately)
    return render_template('registration.html')  # Ensure you have a valid template here
      
if __name__ == "__main__":
    app.run(debug=True, port=5000)