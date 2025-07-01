# Algorithm Learning Playground

## Why I Built This

Ever sat in algorithms class thinking "Okay, cool pseudocode... but how do I actually make this work? And is my solution any good?"

Yeah, me too.

So I built this little PHP playground where you can:
- **Actually implement** those algorithms from your textbook
- **See how fast** (or slow) your code really is
- **Compare different approaches** side by side

## What You Get Out of It

### 🧠 **Learn by Doing**
Instead of just reading about algorithms, you write them. There's a huge difference between understanding "oh, Egyptian multiplication uses binary" and actually implementing it step by step.

### ⚡ **Performance Reality Check**
Want to see why O(n) beats O(n²) in the real world? Run a Gaussian sum formula against a simple loop with n=1,000,000 and watch the magic happen. Spoiler: one finishes instantly, the other... doesn't.

### 🔍 **Pattern Recognition**
After implementing a few algorithms, you start seeing patterns. Setup, validation, measurement, results - it's like learning the rhythm of problem-solving.

### 💪 **Build Confidence**
Nothing beats that feeling when your pseudocode actually works and you can benchmark it like a pro.

## Adding Your Own Problems

Each problem lets you:
- Set up parameters
- Run single tests
- Benchmark multiple runs
- Compare all methods head-to-head

The best part? Adding new algorithms is stupid simple. The framework handles all the boring stuff (timing, formatting, user input), so you just focus on the fun part - the algorithm itself.

```php
class YourCoolCalculationProblem extends AbstractCalculationProblem
{
    // Just implement the algorithm methods
    // Framework handles everything else
}
```

Want to implement binary search? Sorting algorithms? Graph traversal? Go for it! The structure is already there.

## Perfect For...

- **CS Students** learning algorithms
- **Self-taught developers** filling knowledge gaps
- **Interview prep** - practice implementing classics
- **Curiosity** - "I wonder how much faster quicksort really is..."
- **Teaching** - show students real performance differences

## Quick Start

```shell script
git clone <this-repo>
composer install
php main.php
```


Then just follow the menus. It's designed to be intuitive - no manual reading required.

## The Real Value

This isn't just about algorithms. It's about building that intuition for:
- **Problem decomposition** - breaking big problems into small steps
- **Performance thinking** - when does efficiency actually matter?
- **Clean code** - readable, maintainable implementations
- **Testing mindset** - measure, don't guess

Plus, you'll have a growing collection of reference implementations for when you need them later.

## Contributing

Got a favorite algorithm? Implement it and share! The more problems we have, the better this becomes as a learning resource.

Whether it's classic sorting, dynamic programming, or some obscure algorithm you found interesting - if it taught you something, it can teach others too.